<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePerson extends FormRequest
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

    public function messages()
    {
        return [
            'roles' => 'One of the selected roles is invalid',
        ];
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
                'nullable', 'sometimes', 'integer', 'min:1' ,
                Rule::unique('people')->ignore($this->route('person')->id),
            ],
            'last' => 'nullable',
            'first' => 'nullable',
            'roles' => 'nullable|array|exists:App\Role,id',
        ];
    }
}
