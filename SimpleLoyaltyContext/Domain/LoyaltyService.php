<?php

namespace Foodanic\SimpleLoyaltyContext\Domain;

use Foodanic\SimpleLoyaltyContext\Domain\Accrual\Accrual;

class LoyaltyService
{
    public function calculate(array $accruals, float $points): array
    {
        $accruals = array_map(fn(Accrual $item) => $item, $accruals);

        $lines = [];
        foreach ($accruals as $accrual) {
            if ($points === 0) break;
            if ($accrual->balance === 0.0) continue;
            if ($accrual->balance <= $points) {
                $lines[$accrual->id] = $accrual->balance;
                $points -= $accrual->balance;
                $accrual->changeBalance(0);
                continue;
            }
            $lines[$accrual->id] = $points;
            $accrual->changeBalance($accrual->balance - $points);
            $points = 0;
        }
        assert($points === 0);

        return $lines;
    }
}