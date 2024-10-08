<?php
use App\Http\Controllers\MovimentacoesController;
use App\Http\Controllers\CadastrosController;
use Illuminate\Support\Facades\Route;

//Ok, mas algumas rotas poderiam ser melhoradas e simplificadas, recomendo ler sobre Resource routes na documentação.

//Aqui poderia ser usado resource route para evitar definicao de muitas rotas;
Route::prefix('cadastro')->group(function(){
    Route::get('/', [CadastrosController::class, 'index'])->name('cadastro-index');
    Route::get('/{id}', [CadastrosController::class, 'show'])->name('cadastro-show');
    Route::get('/create', [CadastrosController::class, 'create'])->name('cadastro-create');
    Route::post('/', [CadastrosController::class, 'store'])->name('cadastro-store');
    Route::get('/{id}/edit',[CadastrosController::class, 'edit'])->where('id', '[0-9]+')->name('cadastro-edit');
    Route::put('/{id}',[CadastrosController::class, 'update'])->where('id', '[0-9]+')->name('cadastro-update');
    Route::delete('/{id}',[CadastrosController::class, 'destroy'])->where('id', '[0-9]+')->name('cadastro-destroy');
});

Route::prefix('movimentacao')->group(function(){
    Route::get('/', [MovimentacoesController::class, 'index']);
    Route::get('/export', [MovimentacoesController::class, 'export']);
    Route::post('/',[MovimentacoesController::class, 'store']);
    Route::delete('/{id}',[MovimentacoesController::class, 'destroy']);
});

Route::fallback(function(){
    return "Erro ao localizar a rota!";
});