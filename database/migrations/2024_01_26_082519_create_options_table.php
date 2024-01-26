<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

     

    public function up(): void
    {
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->decimal("site_share", 12, 0)->default(0);
            $table->decimal("registration_fee", 12, 0)->default(0);
            $table->decimal("deposit_percentage", 3, 0)->default(0);
            $table->timestamps();
        });
        $data = [
            'site_share' => 10000,
            'registration_fee' => 10000,
            'deposit_percentage' => 1.23,
        ];
        DB::table('options')->insert($data);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('options');
    }
};
