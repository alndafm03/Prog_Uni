<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'first_name'     => 'sometimes|string|max:255',
            'last_name'      => 'sometimes|string|max:255',
            'phone'          => 'sometimes|string|regex:/^[0-9]{10}$/|unique:users,phone,' . $this->user()->id,
            'password'       => 'sometimes|string|min:8',
            'role'           => 'sometimes|in:renter,owner',
            'date_of_birth'  => 'sometimes|date|before:today',
            'personal_photo' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            'id_photo'       => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
}
