<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:ordenes.index')->only(['index', 'show']);
    }

    public function index()
    {
        $transactions = Transaction::with(['payment.order.user'])
            ->orderByDesc('id')
            ->get();

        return view('admin.transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load([
            'payment.order.user',
        ]);

        return view('admin.transactions.show', compact('transaction'));
    }
}
