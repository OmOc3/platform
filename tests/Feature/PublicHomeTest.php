<?php

namespace Tests\Feature;

use Database\Seeders\CommerceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_renders_real_sections_and_guest_ctas(): void
    {
        $this->seed(CommerceSeeder::class);

        $response = $this->get(route('welcome'));

        $response->assertOk();
        $response->assertSee('أحدث الباقات الرقمية');
        $response->assertSee('كتب ومذكرات مختارة');
        $response->assertSee('إنشاء حساب طالب');
        $response->assertSee('باقة الفيزياء الشهرية');
    }
}
