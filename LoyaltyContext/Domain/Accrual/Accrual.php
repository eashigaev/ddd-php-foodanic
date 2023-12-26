<?php

namespace Foodanic\LoyaltyContext\Domain\Accrual;

use DateTime;
use Foodanic\Kernel\Infra\OptimisticLockingTrait;

class Accrual
{
    use OptimisticLockingTrait;

    public string $id;
    public string $accountId;
    public string $paymentId;   //unique
    public float $points;
    public DateTime $momentAt;
    public ?DateTime $voidedAt;
    public ?DateTime $expiredAt;

    public static function record(string $id, string $accountId, string $paymentId, float $amount, DateTime $momentAt): static
    {
        $self = new static();
        $self->id = $id;
        $self->accountId = $accountId;
        $self->paymentId = $paymentId;
        $self->points = round($amount * 5, 2);
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

    public function expire(DateTime $momentAt): void
    {
        assert(!$this->isExpired());
        assert((clone $momentAt)->modify('-6 months') > $momentAt);

        $this->expiredAt = $momentAt;
    }
}