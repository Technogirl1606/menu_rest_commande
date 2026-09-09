<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class StoreItemRequest extends FormRequest
{
    /**
     * Déjà protégé par le middleware "auth" sur la route,
     * donc pas de vérification supplémentaire ici.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_available' => ['boolean'],
            // "image" : vérifie que c'est un VRAI fichier image (jpg, png, webp...),
            // pas juste un fichier renommé avec l'extension .jpg. max:2048 = 2 Mo.
            // "jfif" ajouté explicitement : Laravel ne le reconnaît pas via la règle
            // "image" seule, alors que c'est un format JPEG très courant sous Windows.
            'image' => ['nullable', 'mimes:jpg,jpeg,png,bmp,gif,svg,webp,jfif', 'max:2048'],
        ];
    }
}