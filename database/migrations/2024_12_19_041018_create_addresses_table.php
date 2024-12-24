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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('street', 200)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('country', 100)->nullable(false);
            $table->string('postal_code', 10)->nullable();
            $table->string('label')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable(false)->index('addresses_user_id_foreign');
            $table->timestamps();

            $table->foreign('contact_id', 'addresses_user_id_foreign')->references('id')->on('contacts')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
