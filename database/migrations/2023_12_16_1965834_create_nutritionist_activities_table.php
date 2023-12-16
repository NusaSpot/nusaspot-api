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
        Schema::create('nutritionist_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nutritionist_id');
            $table->string('title');
            $table->date('date');
            $table->string('proof_1');
            $table->string('proof_2')->nullable();
            $table->string('proof_3')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutritionist_activities');
    }
};
