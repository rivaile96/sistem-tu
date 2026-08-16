<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentBill>
 */
class StudentBillFactory extends Factory
{
    protected $model = StudentBill::class;

    public function definition(): array
    {
        return [
            'student_id'        => Student::factory(),
            'type'              => 'SPP',
            'name'              => 'SPP ' . $this->faker->monthName() . ' 2026',
            'amount'            => 150000,
            'status'            => 'UNPAID',
            'payment_method'    => 'CASH',
            'payment_token'     => null,
            'midtrans_order_id' => null,
            'paid_at'           => null,
        ];
    }

    /** Bill with an active Midtrans payment token set. */
    public function withToken(string $token = 'snap-token-abc'): static
    {
        return $this->state(fn () => ['payment_token' => $token]);
    }

    /** Bill that is already fully paid. */
    public function paid(string $orderId = null): static
    {
        return $this->state(fn () => [
            'status'            => 'PAID',
            'paid_at'           => now(),
            'payment_method'    => 'MIDTRANS',
            // Use unique() to avoid UNIQUE constraint collisions when creating
            // multiple paid bills in a single test (e.g. enumeration tests).
            'midtrans_order_id' => $orderId ?? 'BILL-' . $this->faker->unique()->numerify('####') . '-' . $this->faker->lexify('??????') . '-' . time(),
            'payment_token'     => null,
        ]);
    }
}
