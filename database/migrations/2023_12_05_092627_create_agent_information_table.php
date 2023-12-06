<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('agent_information', function (Blueprint $table) {
            $table->id();
            $table->enum('is_active', ['active', 'deactive'])->default('deactive');
            $table->string('profile_photo_url')->nullable();
            $table->unsignedInteger('rate')->nullable(); // 'rate' column with a maximum value of 100 (5 digits total, 2 decimals)
            $table->unsignedBigInteger('agent_id');
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_information');
    }
};
