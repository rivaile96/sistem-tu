<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AdminUIPageRenderTest extends TestCase
{
    use DatabaseMigrations;

    public function test_all_admin_redesigned_views_render_successfully()
    {
        $admin = User::create([
            'name' => 'Admin UI Tester',
            'username' => 'admin_ui',
            'email' => 'admin_ui_test@kaffah.id',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $student = Student::create([
            'name' => 'Siswa Test UI',
            'nis' => '9999999999',
            'class_name' => '1',
        ]);

        // List of all redesigned routes
        $routes = [
            route('dashboard'),
            route('students.index'),
            route('students.show', $student->id),
            route('bills.index'),
            route('bills.create'),
            route('finance.settings.index'),
            route('finance.spp.generate.index'),
            route('pos.transaction'),
            route('pos.history.index'),
            route('pos.items.index'),
            route('pos.bundles.index'),
            route('school.settings'),
            route('settings.integration'),
            route('profile.edit'),
        ];

        foreach ($routes as $url) {
            $response = $this->actingAs($admin)->get($url);
            $response->assertStatus(200);
            $content = $response->getContent();
            $this->assertStringContainsString('figtree', $content, "Failed on {$url}: Figtree font not loaded");
            $this->assertStringContainsString('#178BC1', $content, "Failed on {$url}: Primary color #178BC1 not present");
            $this->assertStringNotContainsString('bg-gradient-to-br from-[#38bdf8]', $content, "Failed on {$url}: Legacy gradient found");
        }
    }
}
