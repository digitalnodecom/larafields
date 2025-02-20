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
        Schema::create('larafields', function (Blueprint $table) {
            $table->id();
            $table->enum('object_type', ['post_type', 'taxonomy', 'user', 'settings']);
            $table->string('object_name')->nullable(); // Nullable if `object_type` is 'user'.
            $table->string('object_id');

            $table->string('field_key');
            $table->json('field_value');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('larafields');
    }
};
