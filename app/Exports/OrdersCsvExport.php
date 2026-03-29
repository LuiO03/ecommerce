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
        return ['ID', 'N° Orden', 'Cliente', 'Total', 'Estado', 'Pago', 'ID Pago', 'Fecha de creación'];
    }

    public function array(): array
    {
        $query = Order::with('user')
            ->select('id', 'order_number', 'user_id', 'total', 'status', 'payment_status', 'payment_id', 'created_at');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get()->map(function ($order) {
            return [
                $order->id,
                $order->order_number,
                optional($order->user)->name ?? '—',
                number_format((float) $order->total, 2),
                $order->status,
                $order->payment_status,
                $order->payment_id ?? '—',
                optional($order->created_at)->format('d/m/Y H:i') ?? '—',
            ];
        })->toArray();
    }
}
