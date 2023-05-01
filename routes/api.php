<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TransactionController;
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

Route::group(['middleware' => ['cors']], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::get('banner', [BannerController::class, 'index']);
        Route::group(['middleware' => ['auth:member']], function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('member', [AuthController::class, 'profile']);
            Route::group(['prefix' => 'transaction'], function () {
                Route::get('', [TransactionController::class, 'index']);
                Route::post('deposit', [TransactionController::class, 'deposit']);
                Route::post('instant-deposit', [TransactionController::class, 'instanDeposit']);
                Route::post('withdraw', [TransactionController::class, 'withdraw']);
                Route::get('last-transaction', [TransactionController::class, 'lastTransaction']);
            });
            Route::group(['prefix' => 'bank'], function() {
                Route::get('', [MemberController::class, 'memberBank']);
                Route::post('', [MemberController::class, 'add']);
            });

            Route::group(['prefix' => 'dataset'], function () {
                Route::get('admin-bank', [DatasetController::class, 'adminBank']);
                Route::get('payment-type', [DatasetController::class, 'paymentType']);
                Route::get('user-bank', [DatasetController::class, 'userBank']);
                Route::get('bank', [DatasetController::class, 'getBankPayment']);
            });
        });
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
