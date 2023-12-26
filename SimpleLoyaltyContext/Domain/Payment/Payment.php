<?php

namespace Foodanic\SimpleLoyaltyContext\Domain\Payment;

use DateTime;
use Foodanic\Kernel\Infra\OptimisticLockingTrait;

class Payment
{
    use OptimisticLockingTrait;

    public string $id;
    public string $providerId;
    public string $transactionId; //unique
    public string $cardNumber;
    public float $amount;
    public DateTime $momentAt;

    public static function register(
        string $id, string $providerId, string $transactionId, string $cardNumber, float $amount, DateTime $momentAt
    ): static
    {
        $self = new static();
        $self->id = $id;
        $self->providerId = $providerId;
        $self->transactionId = $transactionId;
        $self->cardNumber = $cardNumber;
        $self->amount = $amount;
        $self->momentAt = $momentAt;
        return $self;
    }
}