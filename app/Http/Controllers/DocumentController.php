<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(protected DocumentService $service)
    {

    }

    public function all()
    {
        $applications = $this->service->getList();
        // echo asset('storage/file.txt');
        // dd($applications);
        return view('blog.allapp',['applications'=>$applications]);
    }

    public function topUsers()
    {
        $users = Application::leftJoin('applications', 'applications.user_id', 'users.id')
            ->select('users.*', 'applications.message')
            ->get();
    }

    public function create()
    {
        return view('blog.create');
    }

    public function store(Request $request)
    {

        try {

            $data = $request->all();
            $request->validate([
                'file' => 'required|mimes:pdf,xlxs,xlsx,xlx,docx,doc,csv,txt,png,gif,jpg,jpeg,zip,pptx|max:2048',
            ]);
            $file = $request->file('file');
            $data['filetype'] = $file->getClientOriginalExtension();
            $name = date("YmdHis").'.'.$data['filetype'];
            $path = $file->storeAs('files',  $name);
            // $path = $file->move(public_path('files'), $name);
            $data['file'] = $name;
            $app = $this->service->createModel($data);

            Http::attach('file', file_get_contents($file), $name)
                ->post(config('app.media_server'), $data);

            Event::dispatch(new EmployeeEvent($app->firstname));
            return redirect('/');
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    public function show($id)
    {
        $application = Application::find($id);
        return view('blog.show', ['post' => $application]);
    }

    public function edit($id)
    {
        $application = Application::find($id);
        return view('blog.edit', ['application' => $application]);
    }

    public function update(Request $request, $id)
    {
        $application = $this->service->getById($id);
        $data = $request->all();
        $request->validate([
            'file' => 'required|mimes:pdf,xlxs,xlsx,xlx,docx,doc,csv,txt,png,gif,jpg,jpeg,zip,pptx|max:2048',
        ]);
        $file = $request->file('file');
        $path = storage_path('app/public/files') . "/$application->file";
        if(is_file($path)) {
            unlink($path);
        }
        $data['filetype'] = $file->getClientOriginalExtension();
        $name = date("YmdHis").'.'.$data['filetype'];
        $path = $file->storeAs('files',  $name);
        $data['filetype'] = $file->getClientOriginalExtension();
        $data['file'] = $name;
        $data = $this->service->update($id, $data);

        Http::put(config('app.media_server'), ['file' => $name]);
        Http::attach('file', file_get_contents($file), $name)
            ->post(config('app.media_server'), $data);

        return redirect('/')->with('success', 'Project aangepast');
    }

    public function delete($id)
    {
        try {
            $application = $this->service->getById($id);
            $path = storage_path('app/public/files') . "/$application->file";
            if(is_file($path)) {
                Http::put(config('app.media_server'), ['file' => $application->file]);
                unlink($path);
            }
            $data = $this->service->delete($id);
            return redirect('/');
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    public function showExcel($name)
    {
        return view('blog.viewFile',compact('name'));
    }

    public function showWord($name)
    {
        return view('blog.viewWord',compact('name'));
    }

    public function fileDownload($name){
        $path = asset('storage/files/'.$name);
        return Storage::download($path);
    }

    public function excel(Request $request){
        $model = Application::get();
        $excelHeader = [];
        $n = 1;
        $excelHeader[] = [
            'N',
            'firstname',
            'lastname',
            'position',
            'city',
            'email',
            'address',
            'phone',
            'created_at',
            'updated_at'
        ];

        foreach ($model as $value) $excelHeader[] = $this->assignData($n++,$value);
        return Excel::download(new UsersExport($excelHeader), 'users.xlsx');
    }

    protected function assignData($n, $value)
    {
        return [
            'N'=>$n,
            'firstname'=>$value->firstname,
            'lastname'=>$value->lastname,
            'position'=>$value->position,
            'city'=>$value->city,
            'email'=>$value->email,
            'address'=>$value->address,
            'phone'=>$value->phone,
            'created_at'=>$value->created_at->format('Y-m-d H:i'),
            'updated_at'=>$value->updated_at->format('Y-m-d H:i')
        ];
    }
}
