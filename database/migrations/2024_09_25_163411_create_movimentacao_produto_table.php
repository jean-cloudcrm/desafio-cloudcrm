<?php

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
        Schema::create('movimentacao_produto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movimentacao_id')->constrained('movimentacoes')->onDelete('cascade'); 
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimentacao_produto');
    }
};
