<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SendTestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebHookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::get('login',[AuthController::class,'login'])->name('login');
Route::post('login',[AuthController::class,'login'])->name('login');
Route::post('webhook/{token}',[WebHookController::class,'index']);



Route::group([
    'middleware' => ['auth']
], function () {

Route::get('logout',[AuthController::class,'logout'])->name('logout');
Route::get('/',[DashboardController::class,'index'])->name('dashboard.index');
Route::get('dashboard/api',[DashboardController::class,'jsApi'])->name('dashboard.api');
Route::get('activity',[ActivityController::class,'index'])->name('activity');
Route::get('activity/list/api',[ActivityController::class,'listApi']);
Route::get('activity/details/api',[ActivityController::class,'detailsApi']); 
Route::get('activity/export',[ActivityController::class,'export']); 
Route::get('projects',[ProjectController::class,'index'])->name('projects');
Route::any('projects/add',[ProjectController::class,'add'])->name('project.add');
Route::any('projects/edit',[ProjectController::class,'edit'])->name('project.edit');
Route::get('send_test',[SendTestController::class,'index'])->name('send_test');
Route::post('send_test/send',[SendTestController::class,'send'])->name('send_test.send');
Route::any('edit_profile',[UserController::class,'edit'])->name('edit_profile');

});