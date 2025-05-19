<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Api\CreateMessageController;
use App\Http\Controllers\Api\CreateReviewController;
use App\Http\Controllers\Api\EditController;
use App\Http\Controllers\Api\FilteredSearchController;
use App\Http\Controllers\Api\IndexMessageController;
use App\Http\Controllers\Api\IndexReviewController;
use App\Http\Controllers\Api\IndexSponsoshipController;
use App\Http\Controllers\Api\RegisterController as ApiRegisterController;
use App\Http\Controllers\Api\UpdateController;
use App\Http\Controllers\Auth\RegisterController as AuthRegisterController;
use App\Models\Specialization;
use App\Http\Controllers\Api\BraintreeApiController;
use App\Http\Controllers\Api\CreateProfileController;
use App\Http\Controllers\Api\EditProfileController;
use App\Http\Controllers\Api\IndexProfileController;
use App\Http\Controllers\Api\ShowProfileController;
use App\Http\Controllers\Api\UpdateProfileController;

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

// Authentication routes
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::get('/login/check', [AuthController::class, 'checkLoginStatus'])->name('api.login.check');
Route::post('/register', [ApiRegisterController::class, 'register'])->name('api.register');
Route::post('/logout', [AuthController::class, 'logout']);

// Specializations route
Route::get('/specializations', function () {
    $specializations = Specialization::select('id', 'name')->get();
    return response()->json([
        'specializations' => $specializations
    ]);
})->name('api.specializations');

// Reviews routes
Route::get('/reviews', [IndexReviewController::class, 'index'])->name('api.reviews.index');
Route::post('/reviews', [CreateReviewController::class, 'create'])->name('api.reviews.create');
Route::get('/reviews/filter/{specialization}/{rating}/{reviews}', [FilteredSearchController::class, 'filter'])->name('api.reviews.filter');

// Messages routes
Route::get('/messages', [IndexMessageController::class, 'index'])->name('api.messages.index');
Route::post('/messages', [CreateMessageController::class, 'create'])->name('api.messages.create');
// Sponsorships routes
Route::get('/sponsorships', [IndexSponsoshipController::class, 'index'])->name('api.sponsorships.index');

// Profiles routes
Route::get('/profiles', [IndexProfileController::class, 'index'])->name('api.profiles.index');
// -protected
Route::middleware('auth')->group(function () {
    Route::get('/profiles/show', [ShowProfileController::class, 'show'])->name('api.profiles.show');
    Route::post('/profiles/create', [CreateProfileController::class, 'create'])->name('api.profiles.create');
    Route::get('/profiles/edit/', [EditProfileController::class, 'edit'])->name('api.profiles.edit');
    Route::post('/profiles/edit/', [UpdateProfileController::class, 'update'])->name('api.profiles.update');
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->load('specializations');
    });
});

Route::get('/braintree/token', [BraintreeApiController::class, 'generateToken']);
Route::post('/braintree/process-payment', [BraintreeApiController::class, 'processPayment']);
