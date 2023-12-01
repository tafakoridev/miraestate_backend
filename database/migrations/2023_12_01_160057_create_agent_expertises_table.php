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
        Schema::create('agent_expertises', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expertiese_id');
            $table->unsignedBigInteger('field_id');
            $table->string('field_type');
            $table->foreign('expertiese_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_expertises');
    }
};
