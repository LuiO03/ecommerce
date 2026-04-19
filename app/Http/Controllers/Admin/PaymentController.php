<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:ordenes.index')->only(['index', 'show']);
    }

    public function index()
    {
        $payments = Payment::query()
            ->with(['order.user'])
            ->orderByDesc('id')
            ->get();

        $ranking = Payment::query()
            ->selectRaw('provider, COUNT(*) as payments_count, SUM(amount) as gross_total, SUM(fee) as fee_total, SUM(COALESCE(net_amount, amount - fee)) as net_total')
            ->groupBy('provider')
            ->orderByDesc('fee_total')
            ->get()
            ->map(function ($item) {
                $gross = (float) ($item->gross_total ?? 0);
                $fee = (float) ($item->fee_total ?? 0);
                $item->fee_rate = $gross > 0 ? ($fee / $gross) * 100 : 0;

                return $item;
            });

        return view('admin.payments.index', compact('payments', 'ranking'));
    }

    public function show(Payment $payment)
    {
        $payment->load([
            'order.user',
            'transactions',
        ]);

        return view('admin.payments.show', compact('payment'));
    }
}
