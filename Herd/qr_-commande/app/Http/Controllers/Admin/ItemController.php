<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    /**
     * Liste tous les plats, triés par catégorie puis par position,
     * pour l'écran de gestion du menu.
     */
    public function index(): Response
    {
        $items = Item::query()
            ->with('category')
            ->orderBy('category_id')
            ->orderBy('position')
            ->get()
            ->map(fn (Item $item) => [
                'id' => $item->id,
                'category_id' => $item->category_id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => (float) $item->price,
                'is_available' => $item->is_available,
                'category_name' => $item->category->name,
                'image_url' => $item->imageUrl(),
            ]);

        return Inertia::render('Admin/Items/Index', [
            'items' => $items,
            'categories' => Category::query()
                ->orderBy('position')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Affiche le formulaire vide d'ajout d'un plat.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Items/Create', [
            'categories' => Category::query()
                ->orderBy('position')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Traite la soumission du formulaire et enregistre le plat.
     */
    public function store(StoreItemRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Item::create([
            ...collect($validated)->except('image')->all(),
            'is_available' => $validated['is_available'] ?? true,
            'image_path' => $request->hasFile('image')
                ? $request->file('image')->store('items', 'public')
                : null,
            // place le nouveau plat après tous les autres de sa catégorie
            'position' => Item::where('category_id', $validated['category_id'])->count(),
        ]);

        return redirect()
            ->route('admin.items.index')
            ->with('success', 'Plat ajouté avec succès.');
    }

    /**
     * Affiche le formulaire pré-rempli de modification d'un plat.
     * "Item $item" : Laravel retrouve automatiquement le plat correspondant
     * à l'id dans l'URL (Route Model Binding), pas besoin de Item::findOrFail() ici.
     */
    public function edit(Item $item): Response
    {
        return Inertia::render('Admin/Items/Edit', [
            'item' => [
                'id' => $item->id,
                'category_id' => $item->category_id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => (float) $item->price,
                'is_available' => $item->is_available,
                'image_url' => $item->imageUrl(),
            ],
            'categories' => Category::query()
                ->orderBy('position')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Traite la soumission du formulaire de modification.
     */
    public function update(StoreItemRequest $request, Item $item): RedirectResponse
    {
        $validated = $request->validated();
        $data = collect($validated)->except('image')->all();

        if ($request->hasFile('image')) {
            // On supprime l'ancienne image du disque avant d'enregistrer la nouvelle,
            // sinon les anciens fichiers s'accumulent indéfiniment sur le serveur.
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $data['image_path'] = $request->file('image')->store('items', 'public');
        }

        $item->update($data);

        return redirect()
            ->route('admin.items.index')
            ->with('success', 'Plat mis à jour avec succès.');
    }

    /**
     * Supprime un plat. L'historique des commandes passées reste intact
     * (voir order_items : item_id passe à null, mais name/price sont figés).
     */
    public function destroy(Item $item): RedirectResponse
    {
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return redirect()
            ->route('admin.items.index')
            ->with('success', 'Plat supprimé.');
    }
}