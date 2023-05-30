<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface {
    public function userRegister($payload);
    public function checkUser($value, $field);
    public function getAmount($id);
    public function updateUser($id, $payload);
}
