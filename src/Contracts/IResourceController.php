<?php

namespace Saritasa\Laravel\Controllers\Contracts;

interface IResourceController
{
    public function setModelClass(string $modelClass): void;
}
