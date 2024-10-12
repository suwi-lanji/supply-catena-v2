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
        Schema::table('brands', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->default(1);
            $table->foreign('team_id')->references('id')
                ->on('teams')->onDelete('cascade');
            
        });
        Schema::table('manufucturers', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->default(1);
            $table->foreign('team_id')->references('id')
                ->on('teams')->onDelete('cascade');
            
        });

        Schema::table('payment_terms', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->default(1);
            $table->foreign('team_id')->references('id')
                ->on('teams')->onDelete('cascade');
            
        });
        Schema::table('sales_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->default(1);
            $table->foreign('team_id')->references('id')
                ->on('teams')->onDelete('cascade');
            
        });
        Schema::table('sales_persons', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->default(1);
            $table->foreign('team_id')->references('id')
                ->on('teams')->onDelete('cascade');
            
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_term');
    }
};
