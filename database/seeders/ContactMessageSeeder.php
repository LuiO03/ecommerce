<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContactMessage;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        ContactMessage::insert([
    [
        'name' => 'Luis Torres',
        'email' => 'luis@example.com',
        'topic' => 'order',
        'order_number' => 'ORD-1001',
        'message' => 'Quisiera saber el estado de mi pedido, aún no llega.',
        'status' => 'new',
        'read_at' => null,
        'replied_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'Ana Pérez',
        'email' => 'ana@example.com',
        'topic' => 'product',
        'order_number' => null,
        'message' => '¿Tienen stock en talla M de la camisa azul?',
        'status' => 'read',
        'read_at' => now()->subHours(5),
        'replied_at' => null,
        'created_at' => now()->subDay(),
        'updated_at' => now(),
    ],
    [
        'name' => 'Carlos Gómez',
        'email' => 'carlos@example.com',
        'topic' => 'billing',
        'order_number' => 'ORD-1005',
        'message' => 'Tuve un problema con el pago.',
        'status' => 'replied',
        'read_at' => now()->subDays(2),
        'replied_at' => now()->subDay(),
        'created_at' => now()->subDays(3),
        'updated_at' => now(),
    ],
]);
    }
}
