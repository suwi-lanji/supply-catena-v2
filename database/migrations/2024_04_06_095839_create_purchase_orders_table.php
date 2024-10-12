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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('delivery_street');
            $table->string('delivery_city');
            $table->string('delivery_province');
            $table->string('delivery_country');
            $table->string('delivery_phone');
            $table->string('purchase_order_number');
            $table->string('reference_number');
            $table->date('purchase_order_date');
            $table->date('expected_delivery_date');
            $table->string('payment_terms');
            $table->string('shipment_preference');
            $table->string('customer_notes');
            $table->json('terms_and_conditions');
            $table->double('discount');
            $table->double('sub_total');
            $table->double('adjustment');
            $table->string('order_status')->default('OPEN');
            $table->boolean('received')->default(false);
            $table->boolean('billed')->default(false);
            $table->double('total');
            $table->json('items');
            $table->unsignedBigInteger('team_id');
            $table->foreign('team_id')->references('id')
                ->on('teams')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')
                ->on('vendors')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
