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
        Schema::create('nutritionist_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nutritionist_id');
            $table->string('profile_picture');
            $table->enum('gender', ['male', 'female']);
            $table->date('date_of_birth');
            $table->integer('phone');
            $table->integer('nik');
            $table->integer('work_experience');
            $table->string('education');
            $table->string('work_place');
            $table->string('cv');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutritionist_profiles');
    }
};
