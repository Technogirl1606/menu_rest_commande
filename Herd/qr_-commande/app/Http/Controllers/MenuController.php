<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Table;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
      /**
     * Affiche la page menu vue par le client (celle qu'il ouvre en scannant le QR code).
     *
     * "?Table $table = null" : rempli automatiquement par Laravel si l'URL contient
     * un code de table valide (route /menu/table/{table:code}), sinon reste null
     * (cas de la route /menu simple, pour le comptoir/à emporter).
     */
    public function show(?Table $table = null): Response
    {
        // Une seule requête : les catégories, avec leurs plats disponibles déjà chargés.
        // On ne garde que celles dont la plage horaire (si définie) inclut l'heure actuelle.
        $categories = Category::query()
            ->orderBy('position')
            ->with(['items' => fn ($query) => $query->available()])
            ->get()
            ->filter(fn (Category $category) => $category->isAvailableNow())
            ->values();
 
        return Inertia::render('Menu', [
            'categories' => $categories->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
            ]),
            'items' => $categories->flatMap(fn (Category $category) => $category->items)
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'category_id' => $item->category_id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => (float) $item->price,
                    'image_url' => $item->imageUrl(),
                ])
                ->values(),
            'table' => $table ? ['name' => $table->name] : null,
        ]);
    }    
}