<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Alpha',
                'slug' => 'free',
                'price' => 0,
                'features' => ['1 Active Satellite', 'Standard Latency', 'Community Alerts'],
                'is_active' => true,
            ],
            [
                'name' => 'Pro Tactical',
                'slug' => 'pro',
                'price' => 49.00,
                'features' => ['10 Active Satellites', 'Sub-millisecond Latency', 'Slack & Telegram Alerts', 'API Access'],
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise Global',
                'slug' => 'enterprise',
                'price' => 199.00,
                'features' => ['Unlimited Satellites', 'Priority Data Access', 'Full White-label HUD', '24/7 Support', 'Custom Zalo/Web Push'],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            \App\Models\Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
