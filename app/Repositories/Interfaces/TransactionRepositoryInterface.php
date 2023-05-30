<?php

namespace App\Repositories\Interfaces;

interface TransactionRepositoryInterface {
    public function getDepositTransactions();
    public function getCreditTransactions();
    public function createTransaction($payload);
}
