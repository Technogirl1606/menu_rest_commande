<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Item;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'position',
        'available_from',
        'available_until',
    ];

    /**
     * Les plats de cette catégorie, triés selon leur ordre d'affichage.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class)->orderBy('position');
    }

    /**
     * Convertit automatiquement ces colonnes TIME en objets Carbon manipulables
     * (pour pouvoir écrire ->format('H:i') dans le contrôleur, par exemple).
     */
    protected function casts(): array
    {
        return [
            'available_from' => 'datetime:H:i',
            'available_until' => 'datetime:H:i',
        ];
    }

    /**
     * Vrai si la catégorie doit être visible au client à l'heure actuelle.
     *
     * - Aucune plage définie (available_from/until = null) → toujours visible
     *   (cas des boissons, disponibles à toute heure).
     * - Plage définie → visible seulement dans cette fenêtre horaire.
     *   Gère aussi le cas d'une plage qui traverse minuit (ex: 18h -> 2h).
     */
    public function isAvailableNow(): bool
    {
        if (! $this->available_from || ! $this->available_until) {
            return true;
        }

        $now = now()->format('H:i:s');
        $from = $this->available_from->format('H:i:s');
        $until = $this->available_until->format('H:i:s');

        if ($from <= $until) {
            // Cas simple : ex. 11h -> 15h, ne traverse pas minuit.
            return $now >= $from && $now <= $until;
        }

        // Cas où la plage traverse minuit : ex. 18h -> 02h.
        // On est dans la plage si on est APRÈS l'heure de début OU AVANT l'heure de fin.
        return $now >= $from || $now <= $until;
    }
}