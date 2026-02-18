<?php

namespace Tests\Feature\Api\V1;

use App\Models\ApiKey;
use App\Models\Satellite;
use App\Models\Storm;
use App\Models\AlertRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiExpansionTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'test_key_expansion_2026';

    protected function setUp(): void
    {
        parent::setUp();

        ApiKey::create([
            'key' => $this->apiKey,
            'name' => 'Test Key',
            'status' => 'ACTIVE'
        ]);

        // Seed necessary data
        Satellite::factory()->create(['norad_id' => '25544', 'status' => 'ACTIVE']);
        Storm::create([
            'name' => 'EXPANSION_STORM',
            'status' => 'active',
            'latitude' => 10,
            'longitude' => 106
        ]);
        AlertRule::create([
            'name' => 'TEST_RULE',
            'parameter' => 'temperature',
            'operator' => '>',
            'threshold' => 35,
            'severity' => 'CRITICAL'
        ]);
    }

    public function test_storm_endpoints_accessible()
    {
        $response = $this->getJson('/api/v1/weather/storms', ['X-API-KEY' => $this->apiKey]);
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'EXPANSION_STORM']);

        $stormId = Storm::first()->id;
        $response = $this->getJson("/api/v1/weather/storms/{$stormId}/vortex", ['X-API-KEY' => $this->apiKey]);
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['vortex_integrity', 'vertical_wind_shear']]);
    }

    public function test_satellite_telemetry_deep_dive()
    {
        $satId = Satellite::first()->id;
        $response = $this->getJson("/api/v1/satellites/{$satId}/telemetry", ['X-API-KEY' => $this->apiKey]);
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['telemetry' => ['latitude', 'longitude', 'velocity']]]);

        $response = $this->getJson("/api/v1/satellites/{$satId}/tle", ['X-API-KEY' => $this->apiKey]);
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['tle' => ['line1', 'line2']]]);
    }

    public function test_system_health_metrics_accessible()
    {
        $response = $this->getJson('/api/v1/health/system', ['X-API-KEY' => $this->apiKey]);
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['Database', 'Redis', 'API Gateway']]);
    }

    public function test_alert_rules_crud_accessible()
    {
        $response = $this->getJson('/api/v1/alerts/rules', ['X-API-KEY' => $this->apiKey]);
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $response = $this->postJson('/api/v1/alerts/rules', [
            'name' => 'NEW_API_RULE',
            'parameter' => 'pressure',
            'operator' => '<',
            'threshold' => 1000,
            'severity' => 'WARNING'
        ], ['X-API-KEY' => $this->apiKey]);
        $response->assertStatus(201);
    }
}
