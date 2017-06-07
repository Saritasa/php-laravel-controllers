<?php
namespace Saritasa\Laravel\Controllers\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate login request
 */
class LoginRequest extends FormRequest
{
    /**
     * Allow all users access here.
     * @return bool
     */
    public function authorize()
    {
        // Allows all users access
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        return [
            'email'    => 'required|email',
            'password' => 'required|string',
        ];
    }
}
