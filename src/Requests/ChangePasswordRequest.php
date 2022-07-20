<?php

namespace Saritasa\LaravelControllers\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Saritasa\Laravel\Validation\Rules\ContainsLowercase;
use Saritasa\Laravel\Validation\Rules\ContainsNumeral;
use Saritasa\Laravel\Validation\Rules\ContainsUppercase;

/**
 * The HTTP Request with information to change a user password.
 *
 * @property-read string $oldPassword The old user password
 * @property-read string $newPassword The new password to set
 */
class ChangePasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'oldPassword' => ['required', 'string'],
            'newPassword' => [
                'required',
                'string',
                'min:8',
                new ContainsNumeral(),
                new ContainsLowercase(),
                new ContainsUppercase(),
            ],
        ];
    }
}
