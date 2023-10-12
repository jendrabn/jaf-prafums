<?php
// tests/Feature/Api/RegionCityGetTest.php
namespace Tests\Feature\Api;

use App\Models\City;
use Database\Seeders\CitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegionCityGetTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/region/cities/';

  /** @test */
  public function can_get_cities_by_province_id()
  {
    $this->seed([ProvinceSeeder::class, CitySeeder::class]);

    $cities = City::where('province_id', 6)->get();

    $response = $this->getJson($this->uri . 6);

    $response->assertOk()
      ->assertExactJson([
        'data' => $this->formatCityData($cities)
      ])
      ->assertJsonCount(6, 'data');
  }

  /** @test */
  public function returns_not_found_error_if_province_id_doenot_exist()
  {
    $this->seed([ProvinceSeeder::class]);

    $response = $this->getJson($this->uri . 50);

    $response->assertNotFound()
      ->assertJsonStructure(['message']);
  }
}