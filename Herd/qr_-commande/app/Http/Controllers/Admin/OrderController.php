<?php

namespace App\Http\Controllers\Admin;

use App\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;


class OrderController extends Controller
{
    /**
     * Liste les commandes actives (pas encore servies ni annulées),
     * pour l'écran cuisine.
     */
    public function index(): Response
    {
        $orders = Order::query()
            ->with('items')
            ->whereIn('status', [
                OrderStatus::Nouvelle,
                OrderStatus::Preparation,
                OrderStatus::Prete,
            ])
            ->orderBy('created_at')
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'ticket_number' => $order->ticketNumber(),
                'table_label' => $order->table_label,
                'note' => $order->note,
                'status' => $order->status->value,
                'created_at' => $order->created_at->toIso8601String(),
                'items' => $order->items->map(fn ($line) => [
                    'name' => $line->name,
                    'quantite' => $line->quantite,
                ]),
            ]);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'stats' => $this->statsForDate(today()),
        ]);
    }

    /**
     * Statistiques pour UNE journée donnée (utilisée par l'écran cuisine avec
     * "aujourd'hui", et par l'historique avec la date choisie par l'admin).
     * On exclut les commandes annulées de tous ces calculs.
     */
    private function statsForDate(Carbon $date): array
    {
        $dayOrders = Order::query()
            ->whereDate('created_at', $date)
            ->where('status', '!=', OrderStatus::Annulee->value)
            ->get();

        $topItem = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereDate('orders.created_at', $date)
            ->where('orders.status', '!=', OrderStatus::Annulee->value)
            ->select('order_items.name', DB::raw('SUM(order_items.quantite) as qty'))
            ->groupBy('order_items.name')
            ->orderByDesc('qty')
            ->first();

        // Approximation : on utilise updated_at (dernière modification de la
        // commande) comme heure de service. Fiable tant que rien d'autre
        // que le changement de statut ne modifie la commande après coup.
        $avgPrepMinutes = Order::query()
            ->whereDate('created_at', $date)
            ->where('status', OrderStatus::Servie->value)
            ->get()
            ->avg(fn (Order $o) => $o->created_at->diffInMinutes($o->updated_at));

        return [
            'orders_count' => $dayOrders->count(),
            'revenue' => (float) $dayOrders->sum('total'),
            'top_item_name' => $topItem->name ?? null,
            'top_item_qty' => $topItem->qty ?? 0,
            'avg_prep_minutes' => $avgPrepMinutes ? round($avgPrepMinutes) : null,
        ];
    }

    /**
     * Fait passer une commande au statut suivant (nouvelle -> préparation
     * -> prête -> servie). S'appuie sur OrderStatus::next() défini plus tôt.
     */
    public function advance(Order $order): RedirectResponse
    {
        $next = $order->status->next();

        if ($next !== null) {
            $order->update(['status' => $next]);
        }

        return back();
    }

    /**
     * Historique : les commandes d'UNE journée précise (aujourd'hui par défaut),
     * avec les mêmes statistiques que la cuisine mais pour ce jour-là.
     */
    public function history(Request $request): Response
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : today();

        $orders = Order::query()
            ->with('items')
            ->whereDate('created_at', $date)
            ->latest()
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'ticket_number' => $order->ticketNumber(),
                'table_label' => $order->table_label,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'total' => (float) $order->total,
                'created_at' => $order->created_at->format('d/m/Y H:i'),
                'items' => $order->items->map(fn ($line) => [
                    'name' => $line->name,
                    'quantite' => $line->quantite,
                ]),
            ]);

        return Inertia::render('Admin/Orders/History', [
            'orders' => $orders,
            'stats' => $this->statsForDate($date),
            'date' => $date->format('Y-m-d'),
        ]);
    }
}