<?php

namespace Foodanic\LoyaltyContext\Domain\Payment;

class PaymentRegistered
{
    public string $paymentId;

    public static function from(string $paymentId): static
    {
        $self = new static();
        $self->paymentId = $paymentId;
        return $self;
    }
}