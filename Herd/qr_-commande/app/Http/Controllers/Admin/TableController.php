<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class TableController extends Controller
{
    public function index(): Response
    {
        $tables = Table::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Table $table) => [
                'id' => $table->id,
                'name' => $table->name,
                'url' => $table->url(),
            ]);

        return Inertia::render('Admin/Tables/Index', [
            'tables' => $tables,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Tables/Create');
    }

    /**
     * Un seul champ à valider ("name") : pas besoin d'un FormRequest
     * séparé pour une règle aussi simple, on la met directement ici.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('tables', 'name')],
        ]);

        Table::create(['name' => $validated['name']]);

        return redirect()
            ->route('admin.tables.index')
            ->with('success', 'Table ajoutée avec succès.');
    }

    public function destroy(Table $table): RedirectResponse
    {
        $table->delete();

        return redirect()
            ->route('admin.tables.index')
            ->with('success', 'Table supprimée.');
    }
}