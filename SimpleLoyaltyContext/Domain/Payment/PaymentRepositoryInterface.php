<?php

namespace Foodanic\SimpleLoyaltyContext\Domain\Payment;

interface PaymentRepositoryInterface
{
    public function save(Payment $payment);

    public function ofId(string $paymentId): ?Payment;
}