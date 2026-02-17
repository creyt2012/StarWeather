<?php

namespace App\Services\Intelligence;

class AisTrafficService
{
    /**
     * Get global maritime traffic data.
     */
    public function getMaritimeTraffic(): array
    {
        $vessels = [];
        $types = ['CARGO', 'TANKER', 'CONTAINER', 'NAVY_ASSET'];
        $statuses = ['UNDERWAY', 'MOORED', 'ANCHORED'];

        for ($i = 0; $i < 100; $i++) {
            $vessels[] = [
                'id' => 'AIS_' . rand(10000, 99999),
                'name' => 'VESSEL_' . rand(10000, 99999),
                'type' => $types[array_rand($types)],
                'latitude' => (mt_rand(-4000, 4000) / 100),
                'longitude' => (mt_rand(-18000, 18000) / 100),
                'status' => $statuses[array_rand($statuses)],
                'cargo' => $this->getRandomCargo(),
                'strategic_value' => (rand(0, 10) > 7) ? 'HIGH' : 'LOW'
            ];
        }

        return $vessels;
    }

    private function getRandomCargo(): string
    {
        $cargos = ['Crude Oil', 'Commercial Goods', 'Medical Supplies', 'Industrial Equipment', 'Liquefied Gas'];
        return $cargos[array_rand($cargos)];
    }
}
