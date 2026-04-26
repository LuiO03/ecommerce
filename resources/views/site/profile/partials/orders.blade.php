<div class="profile-section">
    <div class="card-header">
        <span class="card-title">Historial de pedidos</span>
        <p class="card-description">Revisa tus compras recientes y su estado actual.</p>
    </div>

    @if (isset($orders) && $orders instanceof \Illuminate\Contracts\Pagination\Paginator
            ? $orders->isEmpty()
            : $orders->isEmpty())
        <div class="card-empty">
            <div class="card-empty-icon card-orange">
                <i class="ri-shopping-bag-3-fill"></i>
            </div>
            <h3 class="card-title">Aún no tienes pedidos</h3>
            <p>Explora la tienda y realiza tu primera compra.</p>
            <a href="{{ route('site.home') }}" class="boton-form boton-success py-3 px-5">
                <span class="boton-form-icon"><i class="ri-store-2-fill"></i></span>
                <span class="boton-form-text">Ir a la tienda</span>
            </a>
        </div>
    @else
        <div class="tabla-wrapper">
            <table class="tabla-general w-full tabla-normal" id="table">
                <thead>
                    <tr>
                        <th>N° Pedido</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>#{{ $order->order_number }}</td>
                            <td>{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                @switch($order->status)
                                    @case('pending')
                                        <span class="badge badge-warning">
                                            <i class="ri-time-line"></i>
                                            Pendiente
                                        </span>
                                    @break

                                    @case('processing')
                                        <span class="badge badge-orange">
                                            <i class="ri-loader-4-line"></i>
                                            En proceso
                                        </span>
                                    @break

                                    @case('shipped')
                                        <span class="badge badge-secondary">
                                            <i class="ri-truck-line"></i>
                                            Enviada
                                        </span>
                                    @case('delivered')
                                        <span class="badge badge-secondary">
                                            <i class="ri-checkbox-multiple-line"></i>
                                            Entregada
                                        </span>
                                    @break

                                    @case('refunded')
                                        <span class="badge badge-info">
                                            <i class="ri-refund-2-line"></i>
                                            Reembolsada
                                        </span>
                                    @break

                                    @case('cancelled')
                                        <span class="badge badge-danger">
                                            <i class="ri-close-circle-line"></i>
                                            Cancelada
                                        </span>
                                    @break

                                    @default
                                        <span class="badge badge-secondary">
                                            <i class="ri-question-line"></i>
                                            {{ ucfirst($order->status) }}
                                        </span>
                                @endswitch
                            </td>
                            <td>S/.{{ number_format($order->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($orders instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="profile-pagination">
                {{ $orders->links() }}
            </div>
        @endif
    @endif
</div>
