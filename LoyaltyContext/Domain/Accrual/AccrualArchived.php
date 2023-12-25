<?php

namespace Foodanic\LoyaltyContext\Domain\Accrual;

class AccrualArchived
{
    public string $accrualId;

    public static function from(string $accrualId): static
    {
        $self = new static();
        $self->accrualId = $accrualId;
        return $self;
    }
}