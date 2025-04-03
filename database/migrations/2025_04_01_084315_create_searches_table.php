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
        Schema::create('searches', function (Blueprint $table) {
            $table->id();
            $table->string('query')->nullable(); // La palabra o categoría buscada
            $table->string('type'); // 'keyword', 'category', 'random'
            $table->text('results')->nullable(); // Resultados de la búsqueda (JSON)
            $table->string('email')->nullable(); // Email del usuario si lo proporcionó
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('searches');
    }
};
