<?php

namespace App\Repositories;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;

class UserRepository implements UserRepositoryInterface {

    /**
     * @param $payload
     * @return mixed
     */
    public function userRegister($payload)
    {
        $payload['status'] = 1;
        $payload['password'] = bcrypt($payload['password']);
        $payload['dob'] = Carbon::parse($payload['dob'])->format('Y-m-d');
        return User::create($payload);
    }

    /**
     * @param $payload
     * @return mixed
     */
    public function checkUser($value, $field) {
        return User::where($field, $value)->with('account')->first();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getAmount($id)
    {
        return User::where('id', $id)->first()->amount;
    }

    /**
     * @param $id
     * @param $payload
     * @return mixed
     */
    public function updateUser($id, $payload)
    {
        return User::where('id', $id)->update($payload);
    }
}
