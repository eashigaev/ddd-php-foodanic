<?php

namespace Foodanic\LoyaltyContext\Domain\Accrual;

class AccrualExpirationPlanned
{
    public string $accrualId;

    public static function from(string $accrualId): static
    {
        $self = new static();
        $self->accrualId = $accrualId;
        return $self;
    }
}