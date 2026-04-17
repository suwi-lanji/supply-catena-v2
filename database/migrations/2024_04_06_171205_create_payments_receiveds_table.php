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
        Schema::create('payments_receiveds', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number');
            $table->date('payment_date');
            $table->double('amount_received');
            $table->double('bank_charges')->default(0);
            $table->string('payment_mode');
            $table->string('paid_through');
            $table->string('reference_number');
            $table->string('notes');
            $table->json('items');
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->unsignedBigInteger('team_id');
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
        Schema::dropIfExists('payments_receiveds');
    }
};
