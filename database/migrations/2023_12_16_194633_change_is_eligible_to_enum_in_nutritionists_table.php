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
        Schema::table('nutritionists', function (Blueprint $table) {
            $table->enum('is_eligible', ['not_completed', 'pending', 'rejected', 'approved'])->default('not_completed')->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nutritionists', function (Blueprint $table) {
            $table->dropColumn('is_eligible');
        });
    }
};
