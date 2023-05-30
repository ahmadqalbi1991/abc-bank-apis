<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['app.verify']], function ($route) {
   $route->post('/register', [AuthController::class, 'register']);
   $route->post('/login', [AuthController::class, 'login']);
   Route::group(['middleware' => ['auth:api', 'throttle:10000000000000,1']], function ($route) {
       $route->get('/get-balance/{id}', [AccountController::class, 'getBalance']);
       $route->post('/verify-password', [AuthController::class, 'verifyPassword']);
       $route->post('/deposit', [AccountController::class, 'deposit']);
       $route->get('/transactions', [AccountController::class, 'transactions']);
       $route->post('/get-beneficiary', [AccountController::class, 'getBeneficiary']);
       $route->post('/transfer-amount', [AccountController::class, 'transferAmount']);
   });
});

