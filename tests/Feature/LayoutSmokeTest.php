<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_and_student_shell_pages_render(): void
    {
        $this->get(route('welcome'))->assertOk();
        $this->get(route('student.register'))->assertOk();
        $this->get(route('student.login'))->assertOk();
        $this->get(route('admin.login'))->assertOk();
    }
}
