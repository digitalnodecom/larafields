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
        Schema::table('larafields', function (Blueprint $table) {
            $table->string('form_location_meta')
                  ->after('form_key')
                  ->nullable();

            $table->unique(['form_key', 'form_location_meta']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('larafields', function (Blueprint $table) {
            $table->dropColumn('form_location_meta');
            $table->dropUnique(['form_key', 'form_location_meta']);
        });
    }
};
