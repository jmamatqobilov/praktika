<?php
namespace App\Services;

use App\Models\Document;


class DocumentService extends BaseService
{
    public function __construct(Document $model)
    {
        $this->model = $model;
    }
}
