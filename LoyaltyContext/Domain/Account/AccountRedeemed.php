<?php

namespace Foodanic\LoyaltyContext\Domain\Account;

use DateTime;

class AccountRedeemed
{
    public string $accountId;
    public array $lines;
    public DateTime $momentAt;

    public static function from(string $accountId, array $lines, DateTime $momentAt): static
    {
        $self = new static();
        $self->accountId = $accountId;
        $self->lines = $lines;
        $self->momentAt = $momentAt;
        return $self;
    }
}