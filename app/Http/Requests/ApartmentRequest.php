<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'province'      => 'required|string|max:255',
            'city'          => 'required|string|max:255',
            'address'       => 'required|string|max:500',
            'description'   => 'required|string|max:2000',
            'price'         => 'required|numeric|min:0',
            'count_room'     => 'required|integer|min:1',
            'count_personal' => 'required|integer|min:1',
            'area'          => 'required|integer|min:10',
            'floor'         => 'required|integer|min:0',
            'balcony'       => 'boolean',
            'furnished'     => 'boolean',
            'parking'       => 'boolean',
            'photos'        => 'required|array|min:1',
            'photos.*' => 'image|mimes:jpg,jpeg,png|max:2048',//تحتاج شرح
            'elevator'      => 'boolean',
            // 'is_available'  => 'boolean',
        ];
    }
}
