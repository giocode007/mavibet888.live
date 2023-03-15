<?php

use App\Events\BetUpdated;
use App\Events\ChatMessageEvent;
use App\Http\Controllers\LockScreen;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormController;
use App\Http\Controllers\PhotosController;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\UserManagementController;




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
    return view('auth.login');
})->middleware('guest');

Auth::routes();

// -----------------------------login----------------------------------------//
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'authenticate']);
Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// ------------------------------ register ---------------------------------//
Route::get('/ACCOUNTID/{id}', [App\Http\Controllers\Auth\RegisterController::class, 'accountid'])->name('accountid');
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'storeUser'])->name('register');

// ----------------------------- player ------------------------------//
Route::get('/home', [App\Http\Controllers\PlayerController::class, 'index'])->name('player');
Route::get('profile/settings/{id}', [App\Http\Controllers\PlayerController::class, 'getProfile']);
Route::post('changePlayerProfileInfo', [App\Http\Controllers\PlayerController::class, 'changeProfileInfo'])->name('changePlayerProfileInfo');
Route::get('betting/history', [App\Http\Controllers\PlayerController::class, 'getPlayerBettingHistory'])->name('getPlayerBettingHistory');
Route::get('transaction/history', [App\Http\Controllers\PlayerController::class, 'getPlayerTransactionHistory'])->name('getPlayerTransactionHistory');

// ----------------------------- operator ------------------------------//
Route::get('admin/players/{id}', [App\Http\Controllers\OperatorController::class, 'getPlayers']);
Route::get('admin/getPlayerInfo', [App\Http\Controllers\OperatorController::class, 'getPlayerInfo'])->name('getPlayerInfo');
Route::get('admin/updatePlayer', [App\Http\Controllers\OperatorController::class, 'updatePlayer'])->name('updatePlayer');
Route::get('logs/{id}', [App\Http\Controllers\OperatorController::class, 'getLogs']);
Route::get('profile/{id}', [App\Http\Controllers\OperatorController::class, 'getProfile']);
Route::get('agents/list', [App\Http\Controllers\OperatorController::class, 'allAgents'])->name('allAgents');
Route::get('players', [App\Http\Controllers\OperatorController::class, 'allPlayers'])->name('allPlayers');
Route::post('changeProfileInfo', [App\Http\Controllers\OperatorController::class, 'changeProfileInfo'])->name('changeProfileInfo');
Route::get('history/{id}', [App\Http\Controllers\OperatorController::class, 'getHistory']);
Route::get('player/history/{id}', [App\Http\Controllers\OperatorController::class, 'getPlayerHistory']);
Route::get('commission/{id}', [App\Http\Controllers\OperatorController::class, 'getCommission']);
Route::get('agentDW', [App\Http\Controllers\OperatorController::class, 'agentDepositWithdraw'])->name('agentDepositWithdraw');
Route::get('audit', [App\Http\Controllers\OperatorController::class, 'getAudit'])->name('getAudit');
Route::post('audit/range', [App\Http\Controllers\OperatorController::class, 'computeProfit'])->name('computeProfit');
Route::post('audit/history', [App\Http\Controllers\OperatorController::class, 'profitHistory'])->name('profitHistory');
Route::get('admin/agents', [App\Http\Controllers\OperatorController::class, 'getAgents'])->name('getAgents');
Route::get('/admin', [App\Http\Controllers\OperatorController::class, 'index'])->name('admin');
Route::get('forgot/password', [App\Http\Controllers\OperatorController::class, 'forgetPassword'])->name('forgetPassword');
Route::get('remove/points', [App\Http\Controllers\OperatorController::class, 'removePoints'])->name('removePoints');
Route::get('export/{action}', [App\Http\Controllers\OperatorController::class, 'export']);




// ----------------------------- Declarator ------------------------------//
Route::get('declarator/profile/{id}', [App\Http\Controllers\DeclaratorController::class, 'getProfile']);
Route::post('declarator/changeProfileInfo', [App\Http\Controllers\DeclaratorController::class, 'changeProfileInfo'])->name('declarator/changeProfileInfo');
Route::get('/declarator', [App\Http\Controllers\DeclaratorController::class, 'index'])->name('declarator');


// ----------------------------- event ------------------------------//
// Route::get('/events', [App\Http\Controllers\EventController::class, 'index'])->name('events');
// Route::get('/events/create', [App\Http\Controllers\EventController::class, 'create'])->name('events/create');
// Route::post('/events/save', [App\Http\Controllers\EventController::class, 'save'])->name('events/save');
// Route::get('/events/edit/{id}', [App\Http\Controllers\EventController::class, 'edit'])->name('events/edit');
// Route::post('form/view/update', [App\Http\Controllers\EventController::class, 'viewUpdate'])->name('form/view/update');
Route::resource('/events', App\Http\Controllers\EventController::class);
Route::get('events/{id}', [App\Http\Controllers\EventController::class, 'show']);
Route::get('getFight', [App\Http\Controllers\EventController::class, 'getFight'])->name('getFight');
Route::get('reverseFight', [App\Http\Controllers\EventController::class, 'reverseFight'])->name('reverseFight');
Route::get('fightsbet/{id}', [App\Http\Controllers\EventController::class, 'showFightBet']);
Route::get('fights/{id}', [App\Http\Controllers\EventController::class, 'showActiveFightBet']);



// ----------------------------- arena ------------------------------//
Route::get('arena/{id}', [App\Http\Controllers\ArenaController::class, 'index']);
Route::get('/changeStatus', [App\Http\Controllers\ArenaController::class, 'changeStatus'])->name('changeStatus');
Route::get('/checkFight', [App\Http\Controllers\ArenaController::class, 'checkFight'])->name('checkFight');
Route::get('/fightStatus', [App\Http\Controllers\ArenaController::class, 'fightStatus'])->name('fightStatus');
Route::get('/fightResult', [App\Http\Controllers\ArenaController::class, 'fightResult'])->name('fightResult');
Route::get('/resetAll', [App\Http\Controllers\ArenaController::class, 'resetAll'])->name('resetAll');
Route::get('/checkResult', [App\Http\Controllers\ArenaController::class, 'checkResult'])->name('checkResult');
Route::get('/checkBet', [App\Http\Controllers\ArenaController::class, 'checkBet'])->name('checkBet');
Route::get('/goNext', [App\Http\Controllers\ArenaController::class, 'goNext'])->name('goNext');
Route::get('/refreshUsers', [App\Http\Controllers\ArenaController::class, 'refreshUsers'])->name('refreshUsers');
Route::get('/bet', [App\Http\Controllers\ArenaController::class, 'bet'])->name('bet');
// Route::get('/bet', function() {
//     event(new BetUpdated());

//     return null;
// })->name('bet');

// ----------------------------- agent ------------------------------//
Route::get('/dashboard', [App\Http\Controllers\AgentController::class, 'dashboard'])->name('home');
Route::get('/summary_report', [App\Http\Controllers\AgentController::class, 'summary_report'])->name('summary_report');
Route::get('/commission_withdrawal', [App\Http\Controllers\AgentController::class, 'commissionWithdrawal'])->name('commission_withdrawal');
Route::get('/dashboard', [App\Http\Controllers\AgentController::class, 'dashboard'])->name('home');
Route::get('/load_logs', [App\Http\Controllers\AgentController::class, 'loadLogs'])->name('load_logs');
Route::get('/event_commission_logs', [App\Http\Controllers\AgentController::class, 'eventCommissionLogs'])->name('event_commission_logs');
Route::get('commission_logs', [App\Http\Controllers\AgentController::class, 'commissionLogs'])->name('commission_logs');
Route::get('agent/profile/{id}', [App\Http\Controllers\AgentController::class, 'getProfile']);
Route::post('agent/changeProfileInfo', [App\Http\Controllers\AgentController::class, 'changeProfileInfo'])->name('agent/changeProfileInfo');
Route::get('getPlayerInfo', [App\Http\Controllers\AgentController::class, 'getPlayerInfo'])->name('getAgengInfo');
Route::get('updatePlayer', [App\Http\Controllers\AgentController::class, 'updatePlayer'])->name('updateAgent');
Route::get('agents', [App\Http\Controllers\AgentController::class, 'getAgents'])->name('getMyAgents');
Route::get('active/players', [App\Http\Controllers\AgentController::class, 'getActivePlayers'])->name('getActivePlayers');
Route::get('deleted/players', [App\Http\Controllers\AgentController::class, 'getDeletedPlayers'])->name('getDeletedPlayers');
Route::get('agents/agentDW', [App\Http\Controllers\AgentController::class, 'agentDepositWithdraw'])->name('agentCash');
Route::get('bettinng/history/{id}', [App\Http\Controllers\AgentController::class, 'getPlayerHistory']);



// ----------------------------- sidebar ------------------------------//
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/back', [App\Http\Controllers\HomeController::class, 'backPage'])->name('back');



// ----------------------------- lock screen --------------------------------//
Route::get('lock_screen', [App\Http\Controllers\LockScreen::class, 'lockScreen'])->middleware('auth')->name('lock_screen');
Route::post('unlock', [App\Http\Controllers\LockScreen::class, 'unlock'])->name('unlock');

// ----------------------------- forget password ----------------------------//
Route::get('forget-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'getEmail'])->name('forget-password');
Route::post('forget-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'postEmail'])->name('forget-password');

// ----------------------------- reset password -----------------------------//
Route::get('reset-password/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'getPassword']);
Route::post('reset-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'updatePassword']);


if(\Illuminate\Support\Facades\App::environment('local')){

    Route::get('/playground', function(){

        event(new ChatMessageEvent());
    
        return null;
    });

    Route::get('/ws', function(){
        return view('websocket');
    });

    Route::post('/chat-message', function(\Illuminate\Http\Request $request){
        event(new ChatMessageEvent($request->message, auth()->user()));
        return null;
    });
}

