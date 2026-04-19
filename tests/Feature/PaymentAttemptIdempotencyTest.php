<?php

namespace Tests\Feature;

use App\Models\PaymentAttempt;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAttemptIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_attempt_idempotency_key_must_be_unique(): void
    {
        $user = User::factory()->create();

        PaymentAttempt::query()->create([
            'idempotency_key' => 'idempotency-key-duplicate',
            'user_id' => $user->id,
            'payment_method' => 'niubiz',
            'purchase_number' => '100001',
            'status' => 'processing',
        ]);

        $this->expectException(QueryException::class);

        PaymentAttempt::query()->create([
            'idempotency_key' => 'idempotency-key-duplicate',
            'user_id' => $user->id,
            'payment_method' => 'niubiz',
            'purchase_number' => '100002',
            'status' => 'processing',
        ]);
    }

    public function test_payment_attempt_supports_conflict_status(): void
    {
        $user = User::factory()->create();

        $attempt = PaymentAttempt::query()->create([
            'idempotency_key' => 'idempotency-key-conflict',
            'user_id' => $user->id,
            'payment_method' => 'niubiz',
            'purchase_number' => '200001',
            'status' => 'conflict',
        ]);

        $this->assertSame('conflict', $attempt->status);
    }
}
