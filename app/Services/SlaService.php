<?php

namespace App\Services;

use App\Models\Sla;

class SlaService
{
    public const PRIORITIES = ['low', 'normal', 'high', 'critical'];

    public function getCompanySlasFormatted(int $companyId): array
    {
        $records = Sla::where('company_id', $companyId)->get()->keyBy('priority');

        $defaults = [
            'critical' => 2,
            'high'     => 12,
            'normal'   => 48,
            'low'      => 72,
        ];

        $result = [];

        foreach (self::PRIORITIES as $priority) {
            $hours = isset($records[$priority]) ? (int) $records[$priority]->hours : $defaults[$priority];

            if ($hours > 0 && $hours % 24 === 0) {
                $result[$priority] = [
                    'value' => (int) ($hours / 24),
                    'unit'  => 'day',
                    'hours' => $hours,
                ];
            } else {
                $result[$priority] = [
                    'value' => $hours,
                    'unit'  => 'hour',
                    'hours' => $hours,
                ];
            }
        }

        return $result;
    }

    public function saveCompanySlas(int $companyId, array $slasInput): void
    {
        foreach (self::PRIORITIES as $priority) {
            $val  = (float) $slasInput[$priority]['value'];
            $unit = $slasInput[$priority]['unit'];

            $totalHours = (int) round($unit === 'day' ? $val * 24 : $val);

            Sla::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'priority'   => $priority,
                ],
                [
                    'hours' => max(1, $totalHours),
                ]
            );
        }
    }
}