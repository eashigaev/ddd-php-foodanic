<?php

namespace Foodanic\LoyaltyContext\Domain\Account;

use DateTime;

class AccountLine
{
    public string $id;
    public string $accountId;
    public string $paymentId;
    public float $initial;
    public DateTime $momentAt;
    public ?DateTime $voidedAt;
    public ?DateTime $expiredAt;

    public float $points;

    public static function make(string $id, string $accountId, string $paymentId, float $amount, DateTime $momentAt): static
    {
        $self = new static();
        $self->id = $id;
        $self->accountId = $accountId;
        $self->paymentId = $paymentId;
        $self->initial = round($amount * 5, 2);
        $self->points = $self->initial;
        $self->momentAt = $momentAt;
        $self->voidedAt = null;
        $self->expiredAt = null;
        return $self;
    }

    public function isVoided(): bool
    {
        return !!$this->voidedAt;
    }

    public function isExpired(): bool
    {
        return !!$this->expiredAt;
    }

    public function void(DateTime $momentAt): void
    {
        assert(!$this->isVoided());
        assert((clone $momentAt)->modify('-2 hours') < $momentAt);

        $this->voidedAt = $momentAt;
    }

    public function redeemPoints(float $points): void
    {
        assert(!$this->isVoided() && !$this->isExpired());
        assert($this->points >= $points);

        $this->points -= $points;
    }

    public function refundPoints(float $points): void
    {
        $this->points += $points;
    }
}