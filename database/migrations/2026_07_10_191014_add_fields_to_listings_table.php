<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('category')->default('General');
            $table->string('location')->default('Campus');
            $table->string('condition')->default('New');
            $table->string('image_url')->nullable();
            $table->boolean('is_verified_seller')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['category', 'location', 'condition', 'image_url', 'is_verified_seller']);
        });
    }
};
