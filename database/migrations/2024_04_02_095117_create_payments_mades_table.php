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
        Schema::create('payments_mades', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number');
            $table->date('payment_date');
            $table->double('payment_made');
            $table->string('payment_mode');
            $table->string('paid_through');
            $table->boolean('clear_applied_amount')->default(false);
            $table->string('reference_number');
            $table->string('notes');
            $table->json('items');
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');$table->unsignedBigInteger('team_id');
            $table->foreign('team_id')->references('id')
                ->on('teams')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments_mades');
    }
};
