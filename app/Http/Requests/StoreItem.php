<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItem extends FormRequest
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
            'id' => 'nullable|sometimes|integer|min:1|unique:App\Item,id',
            'identifier' => 'required|string|max:255|unique:App\Item,identifier',
            'item_id' => 'nullable|exists:App\Artefact,id',
        ];
    }
}
