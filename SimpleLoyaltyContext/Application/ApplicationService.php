<?php

namespace Foodanic\SimpleLoyaltyContext\Application;

use Foodanic\Kernel\Infra\MomentInterface;
use Foodanic\SimpleLoyaltyContext\Domain\Account\Account;
use Foodanic\SimpleLoyaltyContext\Domain\Account\AccountRepositoryInterface;
use Foodanic\SimpleLoyaltyContext\Domain\Accrual\Accrual;
use Foodanic\SimpleLoyaltyContext\Domain\Accrual\AccrualRepositoryInterface;
use Foodanic\SimpleLoyaltyContext\Domain\LoyaltyService;
use Foodanic\SimpleLoyaltyContext\Domain\Payment\Payment;
use Foodanic\SimpleLoyaltyContext\Domain\Payment\PaymentRepositoryInterface;
use Foodanic\SimpleLoyaltyContext\Domain\Redemption\Redemption;
use Foodanic\SimpleLoyaltyContext\Domain\Redemption\RedemptionRepositoryInterface;


/** Each method is wrapped in a transaction. Optimistic locking on aggregate save */
readonly class ApplicationService
{
    public function __construct(
        private MomentInterface               $moment,
        private LoyaltyService                $loyaltyService,
        private AccountRepositoryInterface    $accountRepository,
        private PaymentRepositoryInterface    $paymentRepository,
        private AccrualRepositoryInterface    $accrualRepository,
        private RedemptionRepositoryInterface $redemptionRepository
    )
    {
    }

    // Account

    public function makeAccount(string $cardName): string
    {
        $account = Account::make(uniqid(), $cardName);

        $this->accountRepository->save($account);
        return $account->id;
    }

    // Accrual

    public function addAccrual(string $providerId, string $transactionId, string $cardNumber, string $amount): string
    {
        $account = $this->accountRepository->ofCardNumber($cardNumber);
        assert($account);

        $payment = Payment::register(
            uniqid(), $providerId, $transactionId, $cardNumber, $amount, $this->moment->now()
        );
        $this->paymentRepository->save($payment);

        $accrual = Accrual::add(
            uniqid(), $account->id, $payment->id, $payment->amount, $this->moment->now()
        );
        $this->accrualRepository->save($accrual);

        return $accrual->id;
    }

    public function voidAccrual(string $accrualId): void
    {
        $accrual = $this->accrualRepository->ofId($accrualId);
        assert($accrual);

        $accrual->void($this->moment->now());

        $this->accrualRepository->save($accrual);
    }

    /** @schedule */
    public function markExpiredAccruals(): void
    {
        $this->accrualRepository->markManyAsExpiredTill(
            $this->moment->now()->modify('-6 months')
        );
    }

    // Redemption

    public function makeRedemption(string $accountId, float $points): void
    {
        $accruals = $this->accrualRepository->manyOrderedActiveForAccountUsingLock($accountId);
        assert($accruals);

        $lines = $this->loyaltyService->calculate($accruals, $points);

        array_splice($accruals, 0, count($lines));
        $this->accrualRepository->saveMany($accruals);

        $redemption = Redemption::make(uniqid(), $accountId, $this->moment->now(), $lines);
        $this->redemptionRepository->save($redemption);
    }

    public function voidRedemption(string $redemptionId): void
    {
        $redemption = $this->redemptionRepository->ofId($redemptionId);
        assert($redemption);

        $redemption->void($this->moment->now());
        $this->redemptionRepository->save($redemption);

        $accruals = $this->accrualRepository->manyOfIdsForAccountUsingLock(
            $redemption->accountId, array_keys($redemption->lines)
        );
        foreach ($accruals as $accrual) {
            $points = $redemption->lines[$accrual->id];
            $accrual->changeBalance($accrual->balance + $points);
        }
        $this->accrualRepository->saveMany($accruals);
    }
}