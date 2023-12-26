<?php

namespace Foodanic\LoyaltyContext\Domain\Account;

use DateTime;
use Foodanic\Kernel\Infra\OptimisticLockingTrait;

class Account
{
    use OptimisticLockingTrait;

    public string $id;
    public string $cardName; //unique
    public array $lines;

    public static function make(string $id, string $cardName,): static
    {
        $self = new static();
        $self->id = $id;
        $self->cardName = $cardName;
        $self->lines = [];
        return $self;
    }

    public function addLine(string $accrualId, float $points, DateTime $momentAt): void
    {
        $this->lines[$accrualId] = ['accrualId' => $accrualId, 'points' => $points, 'accruedAt' => $momentAt];
    }

    public function removeLine(string $accrualId): void
    {
        $this->lines = array_filter($this->lines,
            fn(array $line) => $line['accrualId'] === $accrualId
        );
    }

    public function redeem(float $points): array
    {
        $result = [];
        $lines = $this->orderedLines();
        foreach ($lines as &$line) {
            if ($points === 0) break;
            if ($line['points'] === 0) continue;
            if ($line['points'] <= $points) {
                $result[$line['accrualId']] = $line['points'];
                $points -= $line['points'];
                $line['points'] = 0;
                continue;
            }
            $result[$line['accrualId']] = $points;
            $line['points'] -= $points;
            $points = 0;
        }
        assert($points === 0);

        $this->lines = $lines;
        return $result;
    }

    public function refund(array $lines): void
    {
        foreach ($lines as $accrualId => $points) {
            if (!array_key_exists($accrualId, $this->lines)) continue;
            $this->lines[$accrualId]['points'] += $points;
        }
    }

    //

    public function orderedLines(): array
    {
        $result = [...$this->lines];
        usort($result, fn($left, $right) => $left['accruedAt'] <=> $right['accruedAt']);
        return $result;
    }
}
