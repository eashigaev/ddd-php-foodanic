<?php

namespace Foodanic\SimpleLoyaltyContext\Domain\Accrual;

use DateTime;

interface AccrualRepositoryInterface
{
    public function save(Accrual $accrual);

    public function saveMany(array $accruals);

    public function ofId(string $accrualId): ?Accrual;

    /** @return Accrual[] */
    public function manyOfIdsForAccountUsingLock(string $accountId, array $accrualIds): array;

    /** @return Accrual[] */
    public function manyOrderedActiveForAccountUsingLock(string $accountId): array;

    //

    public function markManyAsExpiredTill(DateTime $momentAt);
}