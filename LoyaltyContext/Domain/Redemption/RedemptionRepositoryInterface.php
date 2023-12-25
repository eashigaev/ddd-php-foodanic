<?php

namespace Foodanic\LoyaltyContext\Domain\Redemption;

interface RedemptionRepositoryInterface
{
    public function save(Redemption $redemption);

    public function ofId(string $redemptionId): ?Redemption;
}