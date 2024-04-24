<?php
namespace App\Services;

use App\Models\DocumentType;


class DocumentTypeService extends BaseService
{
    public function __construct(DocumentType $model)
    {
        $this->model = $model;
    }
}
