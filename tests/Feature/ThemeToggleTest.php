<?php

namespace Tests\Feature;

use App\Modules\Students\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_toggle_and_init_script_render_on_shared_layouts(): void
    {
        $student = Student::factory()->create();

        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('data-theme-toggle', false)
            ->assertSee('platform-theme', false);

        $this->get(route('student.register'))
            ->assertOk()
            ->assertSee('data-theme-toggle', false)
            ->assertSee('platform-theme', false);

        $this->get(route('student.login'))
            ->assertOk()
            ->assertSee('data-theme-toggle', false)
            ->assertSee('platform-theme', false);

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('data-theme-toggle', false)
            ->assertSee('platform-theme', false);

        $this->actingAs($student, 'student')
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('data-theme-toggle', false)
            ->assertSee('platform-theme', false);
    }
}
