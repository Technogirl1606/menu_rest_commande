<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'image_path',
        'is_available',
        'position',
    ];
 
    /**
     * Conversion automatique des types quand on lit/écrit ces colonnes.
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }
 
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
 
    /**
     * Filtre réutilisable : Item::available()->get()
     * ne retourne que les plats actuellement proposés à la vente.
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('is_available', true);
    }
 
    public function imageUrl(): ?string
    {
        return $this->image_path ? asset('storage/'.$this->image_path) : null;
    }
}
