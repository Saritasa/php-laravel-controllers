<?php

namespace Saritasa\LaravelControllers\Requests\Concerns;

/**
 * The interface to provider customizable for login request.
 *
 * @method array only(string... $fields)
 */
interface ILoginRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array;
}
