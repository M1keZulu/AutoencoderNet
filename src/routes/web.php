<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommentController;

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


Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/clusters', [AdminController::class, 'clusters'])->name('clusters');
Route::get('/comment/get', [HomeController::class, 'show'])->name('comment.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/admin/create', [AdminController::class, 'store'])->name('admin.store');
    Route::post('/admin/delete', [AdminController::class, 'destroy'])->name('admin.destroy');
    Route::post('/admin/update', [AdminController::class, 'update'])->name('admin.update');
    Route::post('/comment/create', [CommentController::class, 'store'])->name('comment.store');
});

URL::forceScheme('https');

require __DIR__.'/auth.php';
