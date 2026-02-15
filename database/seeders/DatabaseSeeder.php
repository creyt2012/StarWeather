<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\ApiKey;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create a Default Free Tenant
        $tenant = Tenant::create([
            'name' => 'Default Trial Tenant',
            'plan' => 'FREE',
            'settings' => [
                'region' => 'Vietnam',
                'alert_enabled' => true,
            ],
            'expires_at' => now()->addYears(1),
        ]);

        // 2. Create a Development API Key
        ApiKey::create([
            'tenant_id' => $tenant->id,
            'name' => 'Development Key',
            'key' => 'vetinh_dev_key_123',
            'secret' => 'secret_123',
            'is_active' => true,
        ]);

        $this->command->info('Default Tenant and API Key (vetinh_dev_key_123) created.');
    }
}
