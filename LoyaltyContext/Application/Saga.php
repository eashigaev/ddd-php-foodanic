<?php

namespace Foodanic\LoyaltyContext\Application;

use Foodanic\LoyaltyContext\Domain\Account\AccountRedeemed;
use Foodanic\LoyaltyContext\Domain\Accrual\AccrualArchived;
use Foodanic\LoyaltyContext\Domain\Accrual\AccrualExpirationPlanned;
use Foodanic\LoyaltyContext\Domain\Accrual\AccrualRecorded;
use Foodanic\LoyaltyContext\Domain\Payment\PaymentRegistered;
use Foodanic\LoyaltyContext\Domain\Redemption\RedemptionVoided;

/** Some events can be fat */
readonly class Saga
{
    public function __construct(
        private ApplicationService $applicationService
    )
    {
    }

    public function applyPaymentRegistered(PaymentRegistered $event): bool
    {
        $this->applicationService->recordAccrual($event->paymentId);
    }

    public function applyAccrualRecorded(AccrualRecorded $event): bool
    {
        $this->applicationService->addAccountLine($event->accrualId);
    }

    public function applyAccrualArchived(AccrualArchived $event): bool
    {
        $this->applicationService->removeAccountLine($event->accrualId);
    }

    public function applyAccrualExpirationPlanned(AccrualExpirationPlanned $event): bool
    {
        $this->applicationService->expireAccrual($event->accrualId);
    }

    public function applyAccountRedeemed(AccountRedeemed $event): bool
    {
        $this->applicationService->recordRedemption($event->accountId, $event->lines);
    }

    public function applyRedemptionVoided(RedemptionVoided $event): bool
    {
        $this->applicationService->refundAccount($event->redemptionId, $event->lines);
    }

    //

    public function scheduleEveryTimeInterval(): bool
    {
        $this->applicationService->planAccrualsExpiration();
    }
}