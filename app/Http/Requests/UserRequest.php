<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'phone'           => 'required|regex:/^[0-9]{10}$/|unique:users,phone',
            'password'        => 'required|string|min:8|confirmed',
            'role'            => 'required|in:renter,owner',
            'date_of_birth'   => 'required|date|before:today',
            'personal_photo'  => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'id_photo'        => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ];
    }
}
