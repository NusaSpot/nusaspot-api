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
            $table->boolean('is_eligible')->default(false);
            $table->text('remark')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nutritionists', function (Blueprint $table) {
            $table->dropColumn('is_eligible');
            $table->dropColumn('remark');
        });
    }
};
