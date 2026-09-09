<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->withCount('items') // ajoute "items_count" sans requête séparée par catégorie
            ->orderBy('position')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'items_count' => $category->items_count,
                'available_from' => $category->available_from?->format('H:i'),
                'available_until' => $category->available_until?->format('H:i'),
            ]);

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Categories/Create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'position' => Category::count(),
            'available_from' => $validated['available_from'] ?? null,
            'available_until' => $validated['available_until'] ?? null,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Catégorie ajoutée avec succès.');
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('Admin/Categories/Edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'available_from' => $category->available_from?->format('H:i'),
                'available_until' => $category->available_until?->format('H:i'),
            ],
        ]);
    }

    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'available_from' => $validated['available_from'] ?? null,
            'available_until' => $validated['available_until'] ?? null,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    /**
     * Supprime la catégorie ET tous ses plats (cascadeOnDelete en base).
     * On nettoie d'abord les fichiers images sur le disque, car la suppression
     * en cascade n'efface que les lignes en base, jamais les fichiers associés.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $category->items()
            ->whereNotNull('image_path')
            ->pluck('image_path')
            ->each(fn (string $path) => Storage::disk('public')->delete($path));

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Catégorie et ses plats supprimés.');
    }
}