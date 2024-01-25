<?php

use App\Http\Controllers\AgentController;
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
use App\Models\User;
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
    $user = User::where('id', $request->user()->id)->with(['information', 'city', 'educations', 'employees'])->first();
    return $user;
});

Route::middleware('auth:sanctum', 'admin')->get('/user/{id}', function ($id) {
    $user = User::where('id', $id)->with(['information', 'city', 'educations', 'employees'])->first();
    return $user;
});


Route::post('/checkcode', [AuthController::class, 'checkCode']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/commodities/bycity/list/{city_id}', [CommodityController::class, 'indexByCity']);
Route::get('/commodities/bycity/list/{city_id}/{type}', [CommodityController::class, 'indexByCityAndType']);
Route::get('/commodities/bycity/list/{city_id}/{type}/{category_id}', [CommodityController::class, 'indexByCityAndTypeAndCategory']);
Route::get('/categories/children/get', [CategoryController::class, 'StepIndex']);

Route::resource('categories', CategoryController::class);
Route::resource('provinces', ProvinceController::class);
Route::resource('cities', CityController::class);

Route::get('/auctions', [AuctionController::class, 'index']);
Route::get('/auctions/{id}', [AuctionController::class, 'show']);
Route::get('/tenders/byuser/list', [TenderController::class, 'indexByUser'])->middleware("auth:sanctum");
Route::get('/tenders', [TenderController::class, 'index']);
Route::get('/tenders/{id}', [TenderController::class, 'show']);
Route::get('/commodities', [CommodityController::class, 'index']);
Route::get('/commodities/{id}', [CommodityController::class, 'show']);
 
Route::get('/users/agents/{city_id}/list', [UserController::class, 'agentList']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::get('/users/agents/list', [UserController::class, 'agents']);
Route::get('/users/agents/list/{category_id}', [UserController::class, 'agentsByCategory']);

Route::group(['middleware' => ['auth:sanctum']], function () {
       //commodity exp
       
    Route::post('/client/commodities/{id}', [CommodityController::class, 'clientChangePublish']);
    Route::get('/checkProfile', [UserController::class, 'checkProfile']);
    Route::get('/client/commodities', [CommodityController::class, 'indexClientCartable']);
    Route::post('/client/rateagent', [AgentController::class, 'clientRateAgent']);
    Route::post('/commodities/store/ex', [CommodityController::class, 'storeEx']);
    Route::post('/set-photo-agent', [UserController::class, 'setPhotoAgent']);
    Route::post('/users/agents/desk', [UserController::class, 'AgentDesk']);
    Route::post('/users/agents/decline', [UserController::class, 'AgentDecline']);
    Route::post('/users/agents/setagent', [UserController::class, 'setAgent']);
    Route::get('/users/agents/in', [UserController::class, 'agentsIn']);
    Route::get('/users/agents/in/counter', [UserController::class, 'agentsInCounter']);
    Route::get('/users/agents/in/title', [UserController::class, 'agentsInTitle']);
    Route::put('/users/agents/information/{agent_id}', [UserController::class, 'agentInformationUpdate']);
    Route::resource('auctions', AuctionController::class)->except(['show', 'index']);
    Route::resource('tenders', TenderController::class)->except(['show', 'index']);
    Route::post('/users/role/set', [UserController::class, 'setRole']);
    Route::post('/auctions/purpose/send', [AuctionController::class, 'Purpose']);
    Route::post('/tenders/purpose/send', [TenderController::class, 'Purpose']);


    Route::put('/users/update/{id}', [UserController::class, 'update']);


    Route::resource('commodities', CommodityController::class)->except(['show', 'index']);
    Route::post('commodities/update/{id}', [CommodityController::class, 'update']);

    Route::get('/user/agent/category-expertises', [UserController::class, 'getCategoryExpertises']);
    Route::get('/user/agent/department-expertises', [UserController::class, 'getDepartmentExpertises']);
    Route::put('/user/categories/{categoryId}/update-price', [UserController::class, 'handleSavePrice']);
    Route::put('/user/departments/{categoryId}/update-price', [UserController::class, 'handleSaveDepartmentPrice']);


    // Create a new education record for a user
    Route::post('users/{userId}/educations', [UserController::class, 'createEducation']);

    // Create a new employee record for a user
    Route::post('users/{userId}/employees', [UserController::class, 'createEmployee']);

    // Delete an education record for a user
    Route::delete('users/{userId}/educations/{educationId}', [UserController::class, 'deleteEducation']);

    // Delete an employee record for a user
    Route::delete('users/{userId}/employees/{employeeId}', [UserController::class, 'deleteEmployee']);
});

Route::group(['middleware' => ['admin', 'auth:sanctum']], function () {
    Route::resource('users', UserController::class);
    Route::post('/savetocategory/{id}', [CategoryController::class, 'saveFieldsToCategory']);
    Route::post('/admin/commodities/{id}', [CommodityController::class, 'adminChangePublish']);
    Route::get('/admin/commodities', [CommodityController::class, 'indexAdminCartable']);
    Route::get('/admin/comments', [AgentController::class, 'getAllRowsWithCommentAndRate']);
});


//agent controller
Route::get('/rating', [AgentController::class, 'ratinggg']);