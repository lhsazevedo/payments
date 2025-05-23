<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Exception\InvalidPayerException;
use App\Domain\Notification\PaymentReceivedNotification;
use App\Domain\Services\Notification\NotificationServiceContract;
use App\Domain\Services\PaymentAuthorizerContract;
use App\Domain\Services\PaymentRepositoryContract;
use App\Domain\Services\TransactionManagerContract;
use App\Domain\Services\UserRepositoryContract;
use App\Domain\ValueObject\Amount;

class TransferAction
{
    public function __construct(
        private NotificationServiceContract $notificationService,
        private PaymentAuthorizerContract $paymentAuthorizer,
        private PaymentRepositoryContract $paymentRepository,
        private TransactionManagerContract $transactionManager,
        private UserRepositoryContract $userRepository,
    ) {}

    public function __invoke(int $payerId, int $payeeId, Amount $amount): void
    {
        // TODO: Consider making HTTP requests outside the transaction
        $this->transactionManager->run(
            fn() => $this->transfer($payerId, $payeeId, $amount),
        );
    }

    private function transfer(
        int $payerId,
        int $payeeId,
        Amount $amount,
    ): void {
        $payer = $this->userRepository->findByIdForUpdateOrFail($payerId);
        $payee = $this->userRepository->findByIdForUpdateOrFail($payeeId);

        if ($payer->isShopkeeper() || $payerId === $payeeId) {
            throw new InvalidPayerException();
        }

        // TODO: In case the authorization fails, we should
        // mark this payment as pending and queue a retry job.
        $payment = new Payment($payer, $payee, $amount);
        $this->paymentAuthorizer->authorize($payment);

        $payer->debit($amount);
        $payee->credit($amount);

        $this->userRepository->save($payer);
        $this->userRepository->save($payee);
        $this->paymentRepository->save($payment);

        $this->notificationService->notify(
            $payee,
            new PaymentReceivedNotification($payer, $payee, $amount),
        );
    }
}
