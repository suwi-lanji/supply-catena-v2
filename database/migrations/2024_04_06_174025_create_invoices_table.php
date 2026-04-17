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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('invoice_number');
            $table->unsignedBigInteger('order_number');
            $table->date('invoice_date');
            $table->unsignedBigInteger('payment_terms_id');
            $table->date('due_date');
            $table->unsignedBigInteger('sales_person_id')->nullable();
            $table->string('subject')->nullable();
            $table->string('customer_notes')->nullable();
            $table->json('terms_and_conditions')->nullable();
            $table->unsignedBigInteger('team_id');

            $table->foreign('customer_id')->references('id')
                ->on('customers')->onDelete('cascade');
            $table->foreign('payment_terms_id')->references('id')
                ->on('payment_terms')->onDelete('cascade');
            $table->foreign('sales_person_id')->references('id')
                ->on('sales_persons')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
