<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class EventSearchRequest extends FormRequest
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
            'objects' => 'required|array',
            'objects.*.id' => 'required|string',
            'objects.*.name' => 'required|string',
            'people' => 'sometimes|array',
            'people.*.id' => 'integer',
            'places' => 'sometimes|array',
            'places.*.id' => 'integer',
        ];
    }
}
