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
        Schema::create('user_category', function (Blueprint $table) {
            $table->id();
            $table->string('category', 100)->comment('User Category or User Privilege');
            $table->string('descriptions')->comment('Description of User Category or User Privilege');
            $table->string('privilege', 100)->comment('Privilege of User Category or User Privilege');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_category');
    }
};
