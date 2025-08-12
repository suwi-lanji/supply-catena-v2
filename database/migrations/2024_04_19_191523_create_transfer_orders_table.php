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
        Schema::create('transfer_orders', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_order_number');
            $table->date('date');
            $table->string('reason')->nullable();
            $table->json('items');
            $table->boolean('delivered')->default(false);
            $table->unsignedBigInteger('source_warehouse_id')->nullable();
            $table->foreign('source_warehouse_id')->references('id')
                ->on('warehouses')->onDelete('cascade');
            $table->unsignedBigInteger('destination_warehouse_id')->nullable();
            $table->foreign('destination_warehouse_id')->references('id')
                ->on('warehouses')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_orders');
    }
};
