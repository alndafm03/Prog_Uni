<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
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
            'renter_id'     => 'required|exists:users,id',
            'apartment_id'  => 'required|exists:apartments,id',
            'date_start'    => 'required|date|after_or_equal:today',
            'date_finish'   => 'required|date|after:date_start',
            'count_personal' => 'required|integer|min:1',
            'total_price'   => 'required|numeric|min:0',
            'status'        => 'in:pending,approved,rejected,cancelled,completed',
            'approved_at'   => 'nullable|date',
            'cancelled_at'  => 'nullable|date',
        ];
    }
}
