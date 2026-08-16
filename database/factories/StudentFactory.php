<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        // birth_date drives the login password: format('dmy')
        $birthDate = $this->faker->dateTimeBetween('-20 years', '-15 years');

        return [
            'nis'        => $this->faker->unique()->numerify('##########'),
            'nisn'       => $this->faker->optional()->numerify('##########'),
            'name'       => $this->faker->name(),
            'gender'     => $this->faker->randomElement(['L', 'P']),
            'class_name' => 'X IPA 1',
            'birth_date' => $birthDate->format('Y-m-d'),
            'status'     => 'active',
            'parent_phone' => $this->faker->phoneNumber(),
        ];
    }

    /** Produce a calon_siswa (applicant — no NIS yet). */
    public function calon(): static
    {
        return $this->state(fn () => [
            'status' => 'calon_siswa',
            'nis'    => null,
        ]);
    }
}
