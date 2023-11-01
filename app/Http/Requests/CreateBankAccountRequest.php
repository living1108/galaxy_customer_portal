<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBankAccountRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'account_number' => 'required|numeric',
            'institution' => 'required|numeric|digits:3',
            'transit' => 'required|numeric|digits:5',
            'routing_number' => 'numeric|digits:9',
            'account_type' => 'required|string|in:checking,savings',
            'country' => 'required|string',
            'line1' => 'required|string',
            'city' => 'required|string',
            'state' => 'string',
            'zip' => 'required|string',
        ];
    }
}
