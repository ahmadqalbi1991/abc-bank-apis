<?php

namespace App\Repositories\Interfaces;

interface AccountRepositoryInterface {
    public function createAccount($payload);
    public function verifyAccount($accountNumber);
}
