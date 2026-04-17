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
        Schema::table('sales_receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->foreign('payment_term_id')->references('id')
                ->on('payment_terms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_receipts', function (Blueprint $table) {
            //
        });
    }
};
