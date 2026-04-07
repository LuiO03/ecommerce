<div class="profile-section">
    <div class="card-header">
        <span class="card-title">Historial de pedidos</span>
        <p class="card-description">Revisa tus compras recientes y su estado actual.</p>
    </div>

    @if(isset($orders) && $orders instanceof \Illuminate\Contracts\Pagination\Paginator ? $orders->isEmpty() : $orders->isEmpty())
        <div class="card-empty">
            <div class="wishlist-empty-icon">
                <i class="ri-shopping-bag-3-line"></i>
            </div>
            <h3 class="card-title">Aún no tienes pedidos</h3>
            <p>Explora la tienda y realiza tu primera compra.</p>
            <a href="{{ route('welcome.index') }}" class="boton-form boton-success py-3 px-5">
                <span class="boton-form-icon"><i class="ri-store-2-fill"></i></span>
                <span class="boton-form-text">Ir a la tienda</span>
            </a>
        </div>
    @else
        <div class="profile-table-wrapper">
            <table class="profile-table">
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
                            <td>
                                <span class="status-pill status-pill--{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td>S/.{{ number_format($order->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($orders instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="profile-pagination">
                {{ $orders->links() }}
            </div>
        @endif
    @endif
</div>
