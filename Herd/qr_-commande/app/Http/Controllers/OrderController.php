<?php

namespace App\Http\Controllers;

use App\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Crée une commande à partir du panier envoyé par le client.
     *
     * Sécurité : on ne fait confiance qu'à "item_id" et "quantite".
     * Le prix est TOUJOURS relu depuis la base de données, jamais
     * accepté depuis la requête du client.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated) {
            $order = Order::create([
                'table_label' => $validated['table_label'] ?? null,
                'note' => $validated['note'] ?? null,
                'status' => OrderStatus::Nouvelle,
                'total' => 0,
            ]);

            $total = 0;

            foreach ($validated['items'] as $line) {
                // available() : on refuse de commander un plat retiré du menu
                // entre le moment où le client a chargé la page et celui
                // où il valide sa commande.
                $item = Item::available()->findOrFail($line['item_id']);

                $order->items()->create([
                    'item_id' => $item->id,
                    'name' => $item->name,   // instantané figé
                    'price' => $item->price, // instantané figé
                    'quantite' => $line['quantite'],
                ]);

                $total += $item->price * $line['quantite'];
            }

            $order->update(['total' => $total]);

            return $order;
        });

        return response()->json([
            'order' => [
                'id' => $order->id,
                'number' => $order->ticketNumber(),
                'total' => (float) $order->total,
            ],
        ]);
    }

    /**
     * Route publique (pas d'authentification) : renvoie juste le statut
     * d'une commande, pour que le client puisse suivre sa progression
     * après validation. Volontairement minimal : aucune donnée sensible.
     */
    public function status(Order $order): JsonResponse
    {
        return response()->json([
            'status' => $order->status->value,
        ]);
    }
}