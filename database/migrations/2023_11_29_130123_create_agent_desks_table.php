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
        Schema::create('agent_desks', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->unsignedBigInteger('agentable_id');
            $table->string('agentable_type');
            $table->string('comment')->nullable();
            $table->boolean('rate')->default(1);
            $table->boolean('accepted')->default(1);

            $table->unsignedBigInteger('agent_id');
            $table->json("fields")->nullable();
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_desks');
    }
};
