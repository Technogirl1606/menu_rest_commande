<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;



class StoreCategoryRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       
       return [
            'name' => [
                'required',
                'string',
                'max:255',
                // ignore($this->route('category')) : en modification, on exclut la
                // catégorie actuelle de la vérification d'unicité, sinon elle
                // rentrerait en conflit avec... elle-même.
                Rule::unique('categories', 'name')->ignore($this->route('category')),
            ],
            // "required_with" : si l'un des deux est rempli, l'autre devient obligatoire.
            // Impossible d'avoir une heure de début sans heure de fin, ou l'inverse.
            'available_from' => ['nullable', 'date_format:H:i', 'required_with:available_until'],
            'available_until' => ['nullable', 'date_format:H:i', 'required_with:available_from'],
        ];
    }
}
