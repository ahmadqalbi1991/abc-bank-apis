<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;
    private $accountRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        AccountRepositoryInterface $accountRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function register(Request $request) {
        try {
            DB::beginTransaction();
            $email = $this->userRepository->checkUser($request->get('email'), 'email');
            if ($email) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Email already taken'], 500);
            }

            $username = $this->userRepository->checkUser($request->get('username'), 'username');
            if ($username) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Username already taken'], 500);
            }

            $user = $this->userRepository->userRegister($request->all());
            if (!$user) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);
            }

            $chequeFrom = 100000 + $user->id;
            $chequeTo = $chequeFrom + env('TOTAL_PAGES', 30);
            $accountNumber = env('ACCOUNT_NUMBER_PREFIX', '1447700000000000') + $user->id;

            $account = [
                'account_number' => $accountNumber,
                'IBAN' => env('IBAN', 'MA ABC'),
                'card_status' => 'unassigned',
                'card_number' => '',
                'cheque_book_status' => 'active',
                'cheque_book_number_from' => strval($chequeFrom),
                'cheque_book_number_to' => strval($chequeTo),
                'user_id' => $user->id
            ];

            $this->accountRepository->createAccount($account);

            DB::commit();
            return response()->json(['status' => true, 'message' => 'User registered successfully'], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {
        try {
            if (filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)) {
                $user = $this->userRepository->checkUser($request->get('email'), 'email');
            } else {
                $user = $this->userRepository->checkUser($request->get('email'), 'username');
            }
            if (!$user || !$user->status) {
                return response()->json(['status' => false, 'message' => 'User not found'], 404);
            }

            if (!Hash::check($request->get('password'), $user->password)) {
                return response()->json(['status' => false, 'message' => 'Password not matched'], 404);
            }

            $token = $user->createToken('Bank user login')->accessToken;
            return response()->json(['user' => $user, 'token' => $token], 200);
        } catch (\Exception $exception) {
            Log::error($exception);
            return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function verifyPassword(Request $request) {
        try {
            $user = $this->userRepository->checkUser($request->get('id'), 'id');
            if (!$user) {
                return response()->json(['status' => false, 'message' => 'User not found'], 404);
            }

            if (Hash::check($request->get('password'), $user->password)) {
                return response()->json(['status' => true, 'message' => 'Password matched'], 200);
            }
        } catch (\Exception $exception) {
            Log::error($exception);
            return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);
        }
    }
}
