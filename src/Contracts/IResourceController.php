<?php

namespace Saritasa\Laravel\Contracts;

interface IResourceController
{
    public function setModelClass(string $modelClass): void;
}
