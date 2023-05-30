<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventStoreRequest extends FormRequest
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
            'class' => [
                'required',
                Rule::in(['acquisition', 'production', 'manipulation', 'observation']),
            ],
            'type' => 'required_if:class,acquisition,production,manipulation',
            'person1' => ['nullable', 'required_if:class,observation', 'array'],
            'person1.id' => ['nullable', 'required_if:class,observation', 'exists:people,id'],
            'person2' => ['nullable', 'required_if:class,acquisition', 'array'],
            'person2.id' => ['nullable', 'required_if:class,acquisition', 'exists:people,id'],
            'person3' => ['nullable', 'array'],
            'person3.id' => ['nullable', 'exists:people,id'],
            'objects' => ['required', 'array'],
            'objects.*.id' => ['required'],
            'origin' => ['nullable', 'array'],
            'origin.id' => ['nullable', 'exists:places,id'],
            'destination' => ['nullable', 'array'],
            'destination.id' => ['nullable', 'exists:places,id'],
            'year' => ['nullable', 'sometimes', 'before:2000'],
            'document' => ['required'],
        ];
    }
}
