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
        Schema::table('searches', function (Blueprint $table) {
            $table->longText('results')->change(); // Change the data type to longText
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('searches', function (Blueprint $table) {
           
            $table->text('results')->change(); //Reverting to text
        });
    }
};
