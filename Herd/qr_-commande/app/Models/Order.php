<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\OrderStatus;

class Order extends Model
{
    use HasFactory;
     protected $fillable = [
        'table_label',
        'note',
        'status',
        'total',
    ];
 
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total' => 'decimal:2',
        ];
    }
 
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
 
    /**
     * Numéro de ticket lisible, affiché au client et en cuisine (ex : #014).
     * Basé sur l'id — pas de colonne dédiée à gérer ni de risque de collision.
     */
    public function ticketNumber(): string
    {
        return '#'.str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }
}
