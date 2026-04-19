<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrdersCsvExport;
use App\Exports\OrdersExcelExport;
use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Facades\Pdf;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:ordenes.index')->only(['index', 'show', 'invoicePreview']);
        $this->middleware('can:ordenes.update')->only(['updateStatus']);
        $this->middleware('can:ordenes.export')->only(['exportExcel', 'exportPdf', 'exportCsv']);
    }

    public function index()
    {
        $orders = Order::with(['user', 'latestPayment'])
            ->select(['id', 'user_id', 'order_number', 'total', 'status', 'pdf_path', 'created_at'])
            ->orderByDesc('id')
            ->get();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load([
            'user',
            'address',
            'items.product',
            'items.variant.features.option',
            'payments.transactions',
            'latestPayment',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    public function invoicePreview(Order $order)
    {
        $order->load([
            'user',
            'address',
            'items.product',
            'items.variant.features.option',
            'payments.transactions',
            'latestPayment',
        ]);

        $companyInfo = \App\Models\CompanySetting::first();

        return view('admin.export.order-invoice', [
            'order' => $order,
            'companyInfo' => $companyInfo,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,paid,processing,shipped,delivered,cancelled'],
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->input('status');

        if ($oldStatus === $newStatus) {
            return back();
        }

        $order->update(['status' => $newStatus]);

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'status_updated',
            'auditable_type' => Order::class,
            'auditable_id'   => $order->id,
            'old_values'     => ['status' => $oldStatus],
            'new_values'     => ['status' => $newStatus],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        Session::flash('toast', [
            'type'    => 'success',
            'title'   => 'Estado actualizado',
            'message' => 'La orden se actualizó a "' . ucfirst($newStatus) . '" correctamente.',
        ]);

        Session::flash('highlightRow', $order->id);

        return back();
    }

    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        $filename = 'ordenes_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'excel_exported',
            'auditable_type' => Order::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'ids'        => $ids,
                'export_all' => $request->boolean('export_all', false),
                'filename'   => $filename,
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        return Excel::download(new OrdersExcelExport($ids), $filename);
    }

    public function exportCsv(Request $request)
    {
        $ids = $request->has('export_all') ? null : $request->input('ids');
        $filename = 'ordenes_' . now()->format('Y-m-d_H-i-s') . '.csv';

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'csv_exported',
            'auditable_type' => Order::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'ids'        => $ids,
                'export_all' => $request->boolean('export_all', false),
                'filename'   => $filename,
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        return Excel::download(new OrdersCsvExport($ids), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportPdf(Request $request)
    {
        if ($request->has('ids')) {
            $orders = Order::with(['user', 'latestPayment'])->whereIn('id', $request->ids)->get();
        } elseif ($request->has('export_all')) {
            $orders = Order::with(['user', 'latestPayment'])->get();
        } else {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'Sin selección',
                'message' => 'No se seleccionaron órdenes para exportar.',
            ]);
            return back()->with('error', 'No se seleccionaron órdenes para exportar.');
        }

        if ($orders->isEmpty()) {
            return back()->with('error', 'No hay órdenes disponibles para exportar.');
        }

        $filename = 'ordenes_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'pdf_exported',
            'auditable_type' => Order::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'ids'        => $request->ids ?? null,
                'export_all' => $request->boolean('export_all', false),
                'filename'   => $filename,
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        return Pdf::view('admin.export.orders-pdf', compact('orders'))
            ->format('a4')
            ->name($filename)
            ->download();
    }
}
