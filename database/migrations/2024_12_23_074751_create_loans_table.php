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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id')->nullable(false)->index('loans_member_id_foreign');
            $table->unsignedBigInteger('librarian_id')->nullable(false)->index('loans_librarian_id_foreign');
            $table->timestamp('loan_date')->nullable(false);
            $table->timestamp('return_date')->nullable();
            $table->timestamps();

            $table->foreign('member_id', 'loans_member_id_foreign')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('librarian_id', 'loans_librarian_id_foreign')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
