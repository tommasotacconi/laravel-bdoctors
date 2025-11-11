<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Api\EditController;
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
use App\Http\Controllers\Api\SponsorshipController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\MessageController;

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
Route::get('/specializations', fn () => response()->json([
    'specializations' => Specialization::select('id', 'name')->get()
]))->name('api.specializations');

// Reviews routes
Route::get('/reviews', [ReviewController::class, 'index'])->name('api.reviews.index');
Route::post('/reviews', [ReviewController::class, 'create'])->name('api.reviews.create');
Route::get('/reviews/filter/{specialization}/{rating?}/{reviews?}', [ReviewController::class, 'filter'])->name('api.reviews.filter');

// Messages routes
Route::get('/messages', [MessageController::class, 'index'])->name('api.messages.index');
Route::post('/messages', [MessageController::class, 'create'])->name('api.messages.create');

// Sponsorships routes
Route::get('/sponsorships', [SponsorshipController::class, 'index'])->name('api.sponsorships.index');
Route::get('/sponsorships/sponsored', [SponsorshipController::class, 'sponsored'])->name('api.sponsorships.sponsored');

// Profiles routes
// -protected
Route::middleware('auth')->group(function () {
    Route::post('/profiles', [CreateProfileController::class, 'create'])->name('api.profiles.create');
    Route::get('/profiles/edit', [EditProfileController::class, 'edit'])->name('api.profiles.edit');
    Route::patch('/profiles', [UpdateProfileController::class, 'update'])->name('api.profiles.update');
});
// -public
Route::get('/profiles', [IndexProfileController::class, 'index'])->name('api.profiles.index');
Route::get('/profiles/{name}', [ShowProfileController::class, 'show'])->name('api.profiles.show');

// Payments
Route::get('/braintree/token', [BraintreeApiController::class, 'generateToken']);
Route::post('/braintree/process-payment', [BraintreeApiController::class, 'processPayment']);
