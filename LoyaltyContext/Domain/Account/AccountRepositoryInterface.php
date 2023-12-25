<?php

namespace Foodanic\LoyaltyContext\Domain\Account;

interface AccountRepositoryInterface
{
    public function save(Account $account);

    public function ofId(string $accountId): ?Account;

    public function ofCardNumber(string $cardNumber): ?Account;
}