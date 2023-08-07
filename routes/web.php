<?php

use App\Bot\Objects\InlineKeyboardButton;
use App\Http\Controllers\BotController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Models\Cuisines;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome'); 
});

Route::get('/bot/setup', [BotController::class, 'setup']);
Route::post('/bot/webhook/{token}', [BotController::class, 'handleWebhook']);   
