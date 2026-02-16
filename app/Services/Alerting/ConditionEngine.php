<?php

namespace App\Services\Alerting;

use Illuminate\Support\Facades\Log;

class ConditionEngine
{
    /**
     * Evaluate a set of rules against current and previous data states.
     * 
     * @param array $currentState ['temp' => 25, 'wind_speed' => 50, ...]
     * @param array|null $previousState
     * @param array $rules [['logic' => 'AND', 'conditions' => [...]]]
     * @return array ['triggered' => bool, 'level' => string, 'reason' => string]
     */
    public function evaluate(array $currentState, ?array $previousState, array $rules): array
    {
        foreach ($rules as $rule) {
            if ($this->evaluateRule($currentState, $previousState, $rule)) {
                return [
                    'triggered' => true,
                    'level' => $rule['level'] ?? 'WARNING',
                    'reason' => $rule['description'] ?? 'Threshold exceeded'
                ];
            }
        }

        return ['triggered' => false];
    }

    protected function evaluateRule(array $current, ?array $previous, array $rule): bool
    {
        $logic = $rule['logic'] ?? 'AND';
        $results = [];

        foreach ($rule['conditions'] as $condition) {
            $results[] = $this->evaluateCondition($current, $previous, $condition);
        }

        if ($logic === 'AND') {
            return !empty($results) && !in_array(false, $results, true);
        }

        if ($logic === 'OR') {
            return in_array(true, $results, true);
        }

        return false;
    }

    protected function evaluateCondition(array $current, ?array $previous, array $condition): bool
    {
        $param = $condition['parameter'];
        $operator = $condition['operator'];
        $value = $condition['value'];
        $type = $condition['type'] ?? 'absolute'; // absolute, trend

        if (!isset($current[$param])) {
            return false;
        }

        if ($type === 'trend') {
            if (!$previous || !isset($previous[$param])) {
                return false;
            }
            $diff = $current[$param] - $previous[$param];
            return $this->compare($diff, $operator, $value);
        }

        return $this->compare($current[$param], $operator, $value);
    }

    protected function compare($actual, $operator, $expected): bool
    {
        switch ($operator) {
            case '>':
                return $actual > $expected;
            case '<':
                return $actual < $expected;
            case '>=':
                return $actual >= $expected;
            case '<=':
                return $actual <= $expected;
            case '==':
                return $actual == $expected;
            default:
                return false;
        }
    }

    /**
     * Default enterprise rules for immediate safety.
     */
    public function getDefaultRules(): array
    {
        return [
            [
                'description' => 'Extreme Storm Condition: High Wind + Heavy Rain',
                'level' => 'CRITICAL',
                'logic' => 'AND',
                'conditions' => [
                    ['parameter' => 'wind_speed', 'operator' => '>', 'value' => 80],
                    ['parameter' => 'rain_intensity', 'operator' => '>', 'value' => 30]
                ]
            ],
            [
                'description' => 'Flash Flood Risk: Rapid Pressure Drop',
                'level' => 'WARNING',
                'logic' => 'AND',
                'conditions' => [
                    ['parameter' => 'pressure', 'operator' => '<', 'value' => -5, 'type' => 'trend']
                ]
            ],
            [
                'description' => 'Extreme Heat Alert',
                'level' => 'CRITICAL',
                'logic' => 'AND',
                'conditions' => [
                    ['parameter' => 'temperature', 'operator' => '>', 'value' => 45]
                ]
            ]
        ];
    }
}
