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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('quotation_number');
            $table->string('reference_number')->nullable();
            $table->date('quotation_date');
            $table->json('items');

            $table->double('total');
            $table->double('sub_total');
            $table->double('adjustment');
            $table->double('shipment_charges');
            $table->double('discount');
            $table->date('expected_shippment_date')->nullable();
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->unsignedBigInteger('delivery_method_id')->nullable();
            $table->unsignedBigInteger('sales_person_id')->nullable();
            $table->string('customer_notes')->nullable();
            $table->json('terms_and_conditions')->nullable();
            $table->foreign('payment_term_id')->references('id')
                ->on('payment_terms')->onDelete('cascade');
            $table->foreign('delivery_method_id')->references('id')
                ->on('delivery_methods')->onDelete('cascade');
            $table->foreign('sales_person_id')->references('id')
                ->on('sales_persons')->onDelete('cascade');
            $table->string('status');
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
        Schema::dropIfExists('quotations');
    }
};
