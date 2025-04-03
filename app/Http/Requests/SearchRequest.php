<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; //no autentication required
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:keyword,category,random',
            'query' => [
                'nullable',
                'string',
                'max:255', 
                'min: 3',
                'regex:/^[a-zA-Z0-9\s]+$/',
                Rule::requiredIf($this->input('type') === 'keyword'),
                Rule::requiredIf($this->input('type') === 'category'),
            ],
            'email' => 'nullable|email',
        ];
    }
    public function messages(): array
    {
        return [
            'type.required' => 'El tipo de búsqueda es obligatorio.',
            'type.in' => 'El tipo de búsqueda debe ser uno de: keyword, category, random.',
            'query.string' => 'La consulta debe ser una cadena de texto.',
            'query.min' => 'La consulta debe tener al menos :min caracteres.',
            'query.regex' => 'La consulta no puede contener caracteres especiales.',
            'query.required' => 'La consulta es obligatoria para el tipo de búsqueda seleccionado.',
            'query.max' => 'La consulta no puede tener más de :max caracteres.',
            'email.email' => 'El correo electrónico no es válido.',
        ];
    }
    public function attributes(): array
    {
        return [
            'type' => 'tipo de búsqueda',
            'query' => 'consulta',
            'email' => 'correo electrónico',
        ];
    }
   /*  public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
            'errors' => $validator->errors(),
        ], 422));
    } */
}
