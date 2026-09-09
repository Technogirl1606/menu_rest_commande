<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Table extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * Génère automatiquement un code unique à la création,
     * sans que l'admin ait jamais à y penser en ajoutant une table.
     */
    protected static function booted(): void
    {
        static::creating(function (Table $table) {
            if (empty($table->code)) {
                $table->code = self::generateUniqueCode();
            }
        });
    }

    protected static function generateUniqueCode(): string
    {
        do {
            $code = Str::random(8);
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * L'URL complète que le QR code de cette table doit encoder.
     */
    public function url(): string
    {
        return route('menu.table', $this->code);
    }
}