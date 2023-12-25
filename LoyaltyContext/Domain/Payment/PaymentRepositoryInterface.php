<?php

namespace Foodanic\LoyaltyContext\Domain\Payment;

interface PaymentRepositoryInterface
{
    public function save(Payment $payment);

    public function ofId(string $paymentId): ?Payment;
}