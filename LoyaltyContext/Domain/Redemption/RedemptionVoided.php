<?php

namespace Foodanic\LoyaltyContext\Domain\Redemption;

class RedemptionVoided
{
    public string $redemptionId;
    public array $lines;

    public static function from(string $redemptionId, array $lines): static
    {
        $self = new static();
        $self->redemptionId = $redemptionId;
        $self->lines = $lines;
        return $self;
    }
}