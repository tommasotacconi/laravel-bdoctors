<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return '<html><body><h1>Project is Active</h1><p>The application is up and running.</p></body></html>';
});

Route::get('placeholder-images', function () {
    $client_id = 'B3PAC4WHUXxSshbT-Pi2VrB5NlBLiArGtoNofU4Tk94';
    $response = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 Edg/91.0.864.59'
    ])->get("https://api.unsplash.com/collections/4UOO-NbHEt0/photos?client_id=$client_id");

    $images = $response->json();

    return view('placeholder-images', compact('images'));
});
