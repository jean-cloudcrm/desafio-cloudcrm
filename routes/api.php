<?php

use App\Http\Controllers\CadastrosController;
use App\Http\Controllers\MovimentacoesController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return '$request->user()';
});


Route::prefix('cadastro')->group(function(){
    Route::get('/', [CadastrosController::class, 'index'])->name('cadastro-index');
    Route::get('/{id}', [CadastrosController::class, 'show']);
    Route::get('/create', [CadastrosController::class, 'create'])->name('cadastro-create');
    Route::post('/', [CadastrosController::class, 'store'])->name('cadastro-store');
    Route::get('/{id}/edit',[CadastrosController::class, 'edit'])->where('id', '[0-9]+')->name('cadastro-edit');
    Route::put('/{id}',[CadastrosController::class, 'update'])->where('id', '[0-9]+')->name('cadastro-update');
    Route::delete('/{id}',[CadastrosController::class, 'destroy'])->where('id', '[0-9]+')->name('cadastro-destroy');
});

Route::prefix('movimentacao')->group(function(){
    Route::get('/', [MovimentacoesController::class, 'index']);
    Route::post('/',[MovimentacoesController::class, 'store']);
    Route::delete('/{id}',[MovimentacoesController::class, 'destroy']);
});