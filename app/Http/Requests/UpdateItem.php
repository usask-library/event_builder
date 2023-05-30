<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItem extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => [
                'required', 'integer', 'min:1' ,
                Rule::unique('item_identifier')->ignore($this->route('item')->id),
            ],
            'identifier' => [
                'required', 'string', 'max:255',
                Rule::unique('item_identifier')->ignore($this->route('item')->identifier),
            ],
            'item_id' => 'nullable|exists:App\Artefact,id',
        ];
    }
}
