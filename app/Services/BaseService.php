<?php
namespace App\Services;

class  BaseService
{
    protected $model;
    protected $resource;

    public function __construct()
    {
    }

    public function getById($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public function getList()
    {
        return $this->model->get();
    }

    protected function resourceWith($data, $isList = false){
        if($this->resource){
            if($isList) return $this->resource::collection($data);
            else return $this->resource::make($data);
        } else return $data;
    }

    public function show($id)
    {
        $model = $this->getById($id);
        if(!$model) {
            return response()->json(['message' => "Not found"], 404);
        }
        return response()->json($this->resourceWith(data: $model, isList: false), 200);
    }

    public function create($data){
        try {
            $model = $this->model->create($data);
            return response()->json($model, 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 501);
        }
    }

    public function createModel($data){
        try {
            $model = $this->model->create($data);
            return $model;
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 501);
        }
    }



    public function getAll()
    {
        $data = $this->getList();
        return response()->json($this->resourceWith(data: $data, isList: true), 200);
    }

    public function update($id, $data){
        try {
            $model = $this->getById($id);
            if(!$model) {
                return response()->json(['message' => "Not found"], 404);
            }
            $modeldata = $model->update($data);
            return response()->json($modeldata, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 501);
        }
        return $modeldata;
    }

    public function delete($id){
        $model = $this->getById($id);
        if($model){
            $model->delete();
            return response()->json($model, 200);
        } else return response()->json(['message' => "Not found"], 404);
    }
}
