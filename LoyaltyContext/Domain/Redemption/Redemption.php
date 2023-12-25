<?php

namespace Foodanic\LoyaltyContext\Domain\Redemption;

use DateTime;
use Foodanic\Kernel\Infra\OptimisticLockingTrait;

class Redemption
{
    use OptimisticLockingTrait;

    public string $id;
    public string $accountId;
    public DateTime $madeAt;
    public ?DateTime $voidedAt;

    public array $lines;

    public static function record(string $id, string $accountId, DateTime $momentAt, array $lines): static
    {
        $self = new static();
        $self->id = $id;
        $self->accountId = $accountId;
        $self->madeAt = $momentAt;
        $self->voidedAt = null;
        $self->lines = $lines;
        return $self;
    }

    public function void(DateTime $momentAt): void
    {
        assert(!$this->isVoided());
        assert((clone $momentAt)->modify('-2 hours') < $this->madeAt);

        $this->voidedAt = $momentAt;
    }

    public function isVoided(): bool
    {
        return !!$this->voidedAt;
    }
}