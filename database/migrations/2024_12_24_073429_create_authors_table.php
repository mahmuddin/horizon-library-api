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
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('bio')->nullable()->comment('describe the author bibliography');
            $table->string('profile_image')->nullable();
            $table->string('social_media')->nullable()->comment('JSON format such as {"facebook": "https://www.facebook.com/author", "twitter": "https://twitter.com/author"}');
            $table->string('nationality')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('categories')->nullable()->comment('array of categories such as ["fiction", "non-fiction", "research"]');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
