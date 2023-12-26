<?php

namespace Foodanic\SimpleLoyaltyContext\Domain\Account;

use Foodanic\Kernel\Infra\OptimisticLockingTrait;

class Account
{
    use OptimisticLockingTrait;

    public string $id;
    public string $cardName; //unique

    public static function make(string $id, string $cardName): static
    {
        $self = new static();
        $self->id = $id;
        $self->cardName = $cardName;
        return $self;
    }
}
