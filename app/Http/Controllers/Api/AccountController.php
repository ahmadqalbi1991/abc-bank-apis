<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function Symfony\Component\String\u;

class AccountController extends Controller
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;
    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;
    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param AccountRepositoryInterface $accountRepository
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        UserRepositoryInterface        $userRepository,
        AccountRepositoryInterface     $accountRepository,
        TransactionRepositoryInterface $transactionRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->accountRepository = $accountRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBalance($id)
    {
        try {
            $amount = $this->userRepository->getAmount($id);
            return response()->json(['status' => true, 'message' => 'Amount fetched', 'amount' => $amount], 200);
        } catch (\Exception $exception) {
            Log::error($exception);
            return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deposit(Request $request)
    {
        try {
            $user = $this->userRepository->checkUser($request->get('id'), 'id');
            if (!$user) {
                return response()->json(['status' => false, 'message' => 'User not found'], 404);
            }

            $account = $this->accountRepository->verifyAccount($request->get('account_number'));
            if ($account) {
                $payload = [
                    'amount' => $user->amount + $request->get('amount')
                ];
                $this->userRepository->updateUser($request->get('id'), $payload);
                $transaction = [
                    'amount' => $request->get('amount'),
                    'type' => 'deposit',
                    'user_id' => null,
                    'transfer_user_id' => auth('api')->id()
                ];
                $this->transactionRepository->createTransaction($transaction);
                return response()->json(['status' => true, 'message' => 'Amount added'], 200);
            }

            return response()->json(['status' => false, 'message' => 'Account not found'], 404);
        } catch (\Exception $exception) {
            Log::error($exception);
            return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transferAmount(Request $request)
    {
        try {
            $user = $this->userRepository->checkUser(auth('api')->id(), 'id');
            $beneficiary = $this->userRepository->checkUser($request->get('id'), 'id');
            if (!$beneficiary) {
                return response()->json(['status' => false, 'message' => 'User not found'], 404);
            }

            if ($user->amount < $request->get('amount') || !$request->get('amount')) {
                return response()->json(['status' => false, 'message' => 'You don`t have sufficient amount'], 404);
            }

            $account = $this->accountRepository->verifyAccount($request->get('account_number'));
            if ($account) {
                $payload = [
                    'amount' => $beneficiary->amount + $request->get('amount')
                ];
                $this->userRepository->updateUser($request->get('id'), $payload);
                $payloadUser = [
                    'amount' => $user->amount - $request->get('amount')
                ];
                $this->userRepository->updateUser(auth('api')->id(), $payloadUser);
                $transaction = [
                    'amount' => $request->get('amount'),
                    'type' => 'credit',
                    'user_id' => auth('api')->id(),
                    'transfer_user_id' => null
                ];
                $this->transactionRepository->createTransaction($transaction);
                $transactionDeposit = [
                    'amount' => $request->get('amount'),
                    'type' => 'deposit',
                    'transfer_user_id' => $request->get('id'),
                    'user_id' => null
                ];
                $this->transactionRepository->createTransaction($transactionDeposit);
                return response()->json(['status' => true, 'message' => 'Amount send'], 200);
            }

            return response()->json(['status' => false, 'message' => 'Account not found'], 404);
        } catch (\Exception $exception) {
            Log::error($exception);
            return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function transactions()
    {
        try {
            $depositTransactions = $this->transactionRepository->getDepositTransactions();
            $creditTransactions = $this->transactionRepository->getCreditTransactions();
            $transactions = $depositTransactions->merge($creditTransactions);

            return response()->json(['status' => true, 'message' => 'Transaction fetched', 'transactions' => $transactions], 200);
        } catch (\Exception $exception) {
            Log::error($exception);
            return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBeneficiary(Request $request)
    {
        try {
            $account = $this->accountRepository->verifyAccount($request->get('account_number'));
            if ($account) {
                return response()->json(['status' => true, 'message' => 'Account fetched', 'account' => $account], 200);
            }

            return response()->json(['status' => false, 'message' => 'Account not found'], 400);
        } catch (\Exception $exception) {
            Log::error($exception);
            return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);
        }
    }
}
