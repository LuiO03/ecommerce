<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersCsvExport implements FromArray, WithHeadings
{
    protected $ids;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function headings(): array
    {
        return ['ID', 'N° Orden', 'Cliente', 'Entrega', 'Código tienda', 'Total', 'Estado', 'Pago', 'ID Pago', 'Fecha de creación'];
    }

    public function array(): array
    {
        $query = Order::with(['user', 'latestPayment'])
            ->select('id', 'order_number', 'user_id', 'total', 'status', 'delivery_type', 'pickup_store_code', 'created_at');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get()->map(function ($order) {
            return [
                $order->id,
                $order->order_number,
                optional($order->user)->name ?? '—',
                $order->delivery_type === 'pickup' ? 'Recojo en tienda' : 'Delivery',
                $order->pickup_store_code ?? '—',
                number_format((float) $order->total, 2),
                $order->status,
                $order->payment_status,
                $order->payment_id ?? '—',
                optional($order->created_at)->format('d/m/Y H:i') ?? '—',
            ];
        })->toArray();
    }
}
