<?php

namespace Database\Factories;

use App\Models\DailyNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyNote>
 */
class DailyNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => null,
            'section_id' => null,
            'employee_id' => null,
            'academic_year_id' => null,
            'date' => fake()->date(),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(),
            'category' => fake()->randomElement(['akademik', 'perilaku', 'kehadiran', 'kesehatan']),
            'status' => fake()->randomElement(['terbuka', 'ditindaklanjuti', 'selesai']),
        ];
    }
}
