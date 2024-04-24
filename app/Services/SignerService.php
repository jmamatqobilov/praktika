<?php
namespace App\Services;

use App\Models\Signer;


class SignerService extends BaseService
{
    public function __construct(Signer $model)
    {
        $this->model = $model;
    }
}
