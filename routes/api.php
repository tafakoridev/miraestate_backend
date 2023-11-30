<?php

use App\Http\Controllers\AuctionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CommodityController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\TenderController;
use App\Http\Controllers\UserController;
use App\Models\Education;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/checkcode', [AuthController::class, 'checkCode']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/commodities/bycity/list/{city_id}', [CommodityController::class, 'indexByCity']);
Route::resource('categories', CategoryController::class);
Route::resource('departments', DepartmentController::class);
Route::resource('provinces', ProvinceController::class);
Route::resource('cities', CityController::class);


Route::get('/auctions', [AuctionController::class, 'index']);
Route::get('/auctions/{id}', [AuctionController::class, 'show']);
Route::get('/tenders', [TenderController::class, 'index']);
Route::get('/tenders/{id}', [TenderController::class, 'show']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/users/agents/desk', [UserController::class, 'AgentDesk']);
    Route::get('/users/agents/in', [UserController::class, 'agentsIn']);
    Route::get('/users/agents/list', [UserController::class, 'agents']);
    Route::resource('auctions', AuctionController::class)->except(['show', 'index']);
    Route::resource('tenders', TenderController::class)->except(['show', 'index']);
    Route::post('/users/role/set', [UserController::class, 'setRole']);
    Route::post('/auctions/purpose/send', [AuctionController::class, 'Purpose']);
    Route::post('/tenders/purpose/send', [TenderController::class, 'Purpose']);
 
  
 
    Route::resource('commodities', CommodityController::class);
    Route::post('commodities/update/{id}', [CommodityController::class, 'update']);
});

Route::group(['middleware' => ['admin', 'auth:sanctum']], function () {
    Route::resource('users', UserController::class);
});
