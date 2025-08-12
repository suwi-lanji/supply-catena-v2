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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('branch_id')->nullable();
            $table->boolean('useYn')->default(true);                // Used / UnUsed (active status)
            $table->string('regrNm', 60)->nullable();              // Registrant Name
            $table->unsignedBigInteger('regr_id')->nullable();              // Registrant ID
            $table->string('modrNm', 60)->nullable();  // Modifier Name (nullable)
            $table->unsignedBigInteger('modr_id')->nullable();
            $table->foreign('regr_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('modr_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
};
