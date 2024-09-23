<?php

use App\Models\Cadastro;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->json('produtos');
            $table->foreignIdFor(Cadastro::class)->constrained()->onDelete('cascade');
            $table->enum('formas_pagamento', ['credito', 'debito','boleto','pix']);
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
        });
        Schema::dropIfExists('movimentacoes');
    }
};
