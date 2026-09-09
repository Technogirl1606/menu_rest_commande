<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\OrderStatus;
use App\Models\Item;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
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
            'table_label' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', new Enum(OrderStatus::class)],
            'items' => ['required', 'array', 'min:1'],
            // On vérifie que chaque item_id existe VRAIMENT dans la table items.
            'items.*.item_id' => ['required', 'integer', 'exists:items,id'],
            'items.*.quantite' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }
 
    public function messages(): array
    {
        return [
            'items.required' => 'Le panier ne peut pas être vide.',
            'items.*.item_id.exists' => "Un des plats n'existe plus au menu.",
        ];
    }
}
