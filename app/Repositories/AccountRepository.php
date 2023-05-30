<?php

namespace App\Repositories;

use App\Models\Account;
use App\Repositories\Interfaces\AccountRepositoryInterface;

class AccountRepository implements AccountRepositoryInterface {

    /**
     * @param $payload
     * @return mixed
     */
    public function createAccount($payload)
    {
        return Account::create($payload);
    }

    public function verifyAccount($accountNumber)
    {
        return Account::where(['account_number' => $accountNumber, 'status' => 'active'])->with('user')->first();
    }
}
