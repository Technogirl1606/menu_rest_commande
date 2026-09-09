<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Order;
use App\Models\Item;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_id',
        'name',
        'price',
        'quantite',
    ];
 
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }
 
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
 
    /**
     * Peut être null si le plat d'origine a été supprimé du menu depuis.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
 
    public function lineTotal(): float
    {
        return (float) $this->price * $this->quantite;
    }
}
