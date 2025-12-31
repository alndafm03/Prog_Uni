<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class updateapartmentreq extends FormRequest
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
            'province'      => 'sometimes|string|max:255',
            'city'          => 'sometimes|string|max:255',
            'address'       => 'sometimes|string|max:500',
            'description'   => 'sometimes|string|max:2000',
            'price'         => 'sometimes|numeric|min:0',
            'count_room'     => 'sometimes|integer|min:1',
            'count_personal' => 'sometimes|integer|min:1',
            'area'          => 'sometimes|integer|min:10',
            'floor'         => 'sometimes|integer|min:0',
            'balcony'       => 'sometimes|boolean',
            'furnished'     => 'sometimes|boolean',
            'parking'       => 'sometimes|boolean',
            'photos'        => 'sometimes|array|min:1',
            'photos.*' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',//تحتاج شرح
            'elevator'      => 'sometimes|boolean',
        ];
    }
}
