<?php


namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class TransactionRepository implements TransactionRepositoryInterface {
    /**
     * @return mixed
     */
    public function getDepositTransactions()
    {
        return Transaction::where(['transfer_user_id' => auth('api')->id(), 'type' => 'deposit'])->get();
    }

    /**
     * @return mixed
     */
    public function getCreditTransactions()
    {
        return Transaction::where(['user_id' => auth('api')->id(), 'type' => 'credit'])->get();
    }

    /**
     * @param $payload
     * @return mixed
     */
    public function createTransaction($payload) {
        return Transaction::create($payload);
    }
}
