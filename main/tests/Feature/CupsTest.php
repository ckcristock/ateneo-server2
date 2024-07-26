<?php

namespace Tests\Feature;

use App\Models\Agendamiento;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class CupsTest extends TestCase
{
    /**
     * Prueba para obtener el listado de colores para los cups
     *
     * @return void
     */
    public function test_get_colors()
    {
        $this->withoutExceptionHandling();
        $response = $this->post(route('colors.get'));
        $response_data = $response->json();
        dd($response_data);
        $this->assertDatabaseHas('colors', $response_data['data']);
        $this->assertDatabaseCount('colors', $response_data['count']);
        $response->assertStatus($response_data['code']);
    }


    public function test_a_single_color()
    {
    }
}
