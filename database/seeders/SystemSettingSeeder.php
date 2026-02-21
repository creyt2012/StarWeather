<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Telemetry & Networking
            [
                'key' => 'telemetry_polling_rate_ms',
                'value' => '1000',
                'type' => 'integer',
                'group' => 'networking',
                'description' => 'Frontend 1Hz polling rate in milliseconds. Lower values increase server load.'
            ],
            [
                'key' => 'enable_websockets',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'networking',
                'description' => 'Enable Laravel Reverb/Pusher for real-time telemetry streaming (reduces HTTP polling overhead).'
            ],
            // AI Core & L4 Engine
            [
                'key' => 'enable_l4_reprojection',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'ai_core',
                'description' => 'Enable Python OpenCV Reprojection pipeline for Geostationary to Equirectangular mapping.'
            ],
            // Storage & Retention
            [
                'key' => 'imagery_retention_days',
                'value' => '7',
                'type' => 'integer',
                'group' => 'storage',
                'description' => 'Number of days to keep raw and processed 4K satellite imagery on disk before auto-deletion.'
            ],
            // External APIs
            [
                'key' => 'noaa_api_delay_ms',
                'value' => '2000',
                'type' => 'integer',
                'group' => 'api_limits',
                'description' => 'Delay between NOAA API requests to prevent IP ban.'
            ]
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
