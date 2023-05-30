<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventStoreBulkAcquisitionRequest extends FormRequest
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
            'type' => ['required', 'in:donation,purchase,other'],
            'person2' => ['required', 'array'],
            'person2.id' => ['required', 'exists:people,id'],
            'objects' => ['required', 'array'],
            'objects.*.id' => ['required'],
            'document' => ['required'],
        ];
    }
}
