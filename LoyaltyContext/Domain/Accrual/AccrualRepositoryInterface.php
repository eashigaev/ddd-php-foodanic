<?php

namespace Foodanic\LoyaltyContext\Domain\Accrual;

use DateTime;

interface AccrualRepositoryInterface
{
    public function save(Accrual $accrual);

    public function ofId(string $accrualId): ?Accrual;

    /** @return string[] */
    public function manyNotExpiredIdsTill(DateTime $momentAt): array;
}