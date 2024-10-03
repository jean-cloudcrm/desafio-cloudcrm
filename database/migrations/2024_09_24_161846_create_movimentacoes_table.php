<?php

use App\Models\Cadastro;
use App\Models\Produto;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Bom, apenas cuidar com os enums na migration, aqui o ideal talvez era criar um arquivo Enum definir os casos e salvar uma string ou int referente ao case;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Cadastro::class)->constrained()->onDelete('cascade');
            $table->enum('formas_pagamento', ['credito', 'debito']); // Definir um enum assim é funcional mas não escalável, se o enum tiver mais casos deveremos atualizar a migration para incluir mais caso ao enum.
            $table->boolean('bloqueado');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimentacoes', function(Blueprint $table){
            $table->dropForeign(['cadastro_id']);
            $table->dropColumn('cadastro_id');
            $table->dropForeign(['produto_id']);
            $table->dropColumn('produto_id');
        });
        Schema::dropIfExists('movimentacoes');
    }
};
