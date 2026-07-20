<x-mail::message>

# Nuevo mensaje de contacto

Se ha recibido un nuevo mensaje desde el formulario de contacto en **{{ $company?->name ?? config('app.name') }}**.

---

## Datos del remitente

- **Nombre:** {{ $data['name'] ?? 'N/A' }}
- **Correo:** {{ $data['email'] ?? 'N/A' }}
- **Tema:** {{ $data['topic_label'] ?? ($data['topic'] ?? 'N/A') }}
@if(!empty($data['order_number']))
- **Pedido:** {{ $data['order_number'] }}
@endif

---

## Mensaje

<x-mail::panel>
{{ $data['message'] ?? '' }}
</x-mail::panel>

---

## Información del sistema

- Fecha: {{ $data['submitted_at'] ?? now()->format('d/m/Y H:i') }}
- IP: {{ $data['ip_address'] ?? 'N/A' }}
@if(!empty($data['user_agent']))
- Navegador: {{ $data['user_agent'] }}
@endif

---

<x-mail::button url="{{ route('admin.contact-messages.index') }}">
Ver en el panel de administración
</x-mail::button>

---

Este mensaje fue enviado automáticamente desde el formulario de contacto.

Gracias,<br>
{{ $company?->name ?? config('app.name') }}

</x-mail::message>
