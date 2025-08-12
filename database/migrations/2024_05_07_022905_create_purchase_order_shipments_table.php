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
        Schema::create('purchase_order_shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->default(1);
            $table->foreign('vendor_id')->references('id')
                ->on('vendors')->onDelete('cascade');
            $table->unsignedBigInteger('purchase_order_id')->default(1);
            $table->foreign('purchase_order_id')->references('id')
                ->on('purchase_orders')->onDelete('cascade');
            $table->string('shipment_order_number');
            $table->date('shipment_date');
            $table->unsignedBigInteger('delivery_method_id');
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->double('shipping_charges')->default(0);
            $table->string('notes')->nullable();
            $table->boolean('delivered')->default(false);

            $table->unsignedBigInteger('team_id')->default(1);
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
        Schema::dropIfExists('purchase_order_shipments');
    }
};
