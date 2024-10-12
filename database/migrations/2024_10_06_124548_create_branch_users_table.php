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
        Schema::create('branch_users', function (Blueprint $table) {
            $table->id();
            $table->string('tpin');                // Taxpayer’s identification number
            $table->string('bhfId');                // Branch ID
            $table->foreignId('user_id', 20)->constrained();              // User ID
            $table->string('userNm', 60);              // Username
            $table->string('adrs', 200);               // Address
            $table->boolean('useYn')->default(true);                // Used / UnUsed (active status)
            $table->string('regrNm', 60);              // Registrant Name
            $table->unsignedBigInteger('regr_id');              // Registrant ID
            $table->string('modrNm', 60);  // Modifier Name (nullable)
            $table->unsignedBigInteger('modr_id');  // Modifier ID (nullable)
            $table->foreignId('team_id')->constrained();
            $table->foreign('regr_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('modr_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_users');
    }
};
