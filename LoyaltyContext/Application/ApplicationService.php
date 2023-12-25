<?php

namespace Foodanic\LoyaltyContext\Application;

use Foodanic\Kernel\Infra\EventBusInterface;
use Foodanic\Kernel\Infra\MomentInterface;
use Foodanic\LoyaltyContext\Domain\Account\Account;
use Foodanic\LoyaltyContext\Domain\Account\AccountRedeemed;
use Foodanic\LoyaltyContext\Domain\Account\AccountRepositoryInterface;
use Foodanic\LoyaltyContext\Domain\Accrual\Accrual;
use Foodanic\LoyaltyContext\Domain\Accrual\AccrualArchived;
use Foodanic\LoyaltyContext\Domain\Accrual\AccrualExpirationPlanned;
use Foodanic\LoyaltyContext\Domain\Accrual\AccrualRecorded;
use Foodanic\LoyaltyContext\Domain\Accrual\AccrualRepositoryInterface;
use Foodanic\LoyaltyContext\Domain\Payment\Payment;
use Foodanic\LoyaltyContext\Domain\Payment\PaymentRegistered;
use Foodanic\LoyaltyContext\Domain\Payment\PaymentRepositoryInterface;
use Foodanic\LoyaltyContext\Domain\Redemption\Redemption;
use Foodanic\LoyaltyContext\Domain\Redemption\RedemptionRepositoryInterface;
use Foodanic\LoyaltyContext\Domain\Redemption\RedemptionVoided;

/** Each method inside a transaction */
readonly class ApplicationService
{
    public function __construct(
        private MomentInterface               $moment,
        private EventBusInterface             $eventBus,
        private AccountRepositoryInterface    $accountRepository,
        private PaymentRepositoryInterface    $paymentRepository,
        private AccrualRepositoryInterface    $accrualRepository,
        private RedemptionRepositoryInterface $redemptionRepository
    )
    {
    }

    // Payment

    public function registerPayment(string $providerId, string $transactionId, string $cardNumber, string $amount): string
    {
        $payment = Payment::register(
            uniqid(), $providerId, $transactionId, $cardNumber, $amount, $this->moment->now()
        );

        $this->paymentRepository->save($payment);
        $this->eventBus->emitAsync([
            PaymentRegistered::from($payment->id)
        ]);
        return $payment->id;
    }

    // Account

    public function makeAccount(string $cardName): string
    {
        $account = Account::make(uniqid(), $cardName);

        $this->accountRepository->save($account);
        return $account->id;
    }

    /** @internal */
    public function addAccountLine(string $accrualId): string
    {
        $accrual = $this->accrualRepository->ofId($accrualId);
        assert($accrual);
        $account = $this->accountRepository->ofId($accrual->accountId);
        assert($account);

        $account->addLine($accrual->id, $accrual->points, $accrual->momentAt);

        $this->accountRepository->save($account);
        return $account->id;
    }

    /** @internal */
    public function removeAccountLine(string $accrualId): string
    {
        $accrual = $this->accrualRepository->ofId($accrualId);
        assert($accrual);
        $account = $this->accountRepository->ofId($accrual->accountId);
        assert($account);

        $account->removeLine($accrual->id);

        $this->accountRepository->save($account);
        return $account->id;
    }

    public function redeemAccount(string $accountId, float $points): void
    {
        $account = $this->accountRepository->ofId($accountId);
        assert($account);

        $lines = $account->redeem($points);
        $this->accountRepository->save($account);
        $this->eventBus->emitAsync([
            AccountRedeemed::from($account->id, $lines, $this->moment->now())
        ]);
    }

    /** @internal */
    public function refundAccount(string $redemptionId): void
    {
        $redemption = $this->redemptionRepository->ofId($redemptionId);
        assert($redemption);
        $account = $this->accountRepository->ofId($redemption->accountId);
        assert($account);

        $account->refund($redemption->lines);
        $this->accountRepository->save($account);
    }

    // Accrual

    /** @internal */
    public function recordAccrual(string $paymentId): string
    {
        $payment = $this->paymentRepository->ofId($paymentId);
        assert($payment);
        $account = $this->accountRepository->ofCardNumber($payment->cardNumber);
        assert($account);

        $accrual = Accrual::record(
            uniqid(), $account->id, $paymentId, $payment->amount, $this->moment->now()
        );
        $this->accrualRepository->save($accrual);
        $this->eventBus->emit([
            AccrualRecorded::from($accrual->id) // Can be fat event
        ]);
        return $accrual->id;
    }

    public function voidAccrual(string $accrualId): void
    {
        $accrual = $this->accrualRepository->ofId($accrualId);
        assert($accrual);

        $accrual->void($this->moment->now());

        $this->accrualRepository->save($accrual);
        $this->eventBus->emitAsync([
            AccrualArchived::from($accrual->id)
        ]);
    }

    public function expireAccrual(string $accrualId): void
    {
        $accrual = $this->accrualRepository->ofId($accrualId);
        assert($accrual);

        $accrual->expire($this->moment->now());

        $this->accrualRepository->save($accrual);
        $this->eventBus->emitAsync([
            AccrualArchived::from($accrual->id)
        ]);
    }

    public function planAccrualsExpiration(): void
    {
        $momentAt = $this->moment->now()->modify('-6 months');

        $events = array_map(
            fn(string $accrualId) => AccrualExpirationPlanned::from($accrualId),
            $this->accrualRepository->manyNotExpiredIdsTill($momentAt)
        );
        $this->eventBus->emitAsync($events);
    }

    // Redemption

    /** @internal */
    public function recordRedemption(string $accountId, array $lines): string
    {
        $account = $this->accountRepository->ofId($accountId);
        assert($account);

        $redemption = Redemption::record(
            uniqid(), $accountId, $this->moment->now(), $lines
        );

        $this->redemptionRepository->save($redemption);
        return $redemption->id;
    }

    public function voidRedemption(string $redemptionId): void
    {
        $redemption = $this->redemptionRepository->ofId($redemptionId);
        assert($redemption);

        $redemption->void($this->moment->now());

        $this->redemptionRepository->save($redemption);
        $this->eventBus->emitAsync([
            RedemptionVoided::from($redemption->id, $redemption->lines)
        ]);
    }
}