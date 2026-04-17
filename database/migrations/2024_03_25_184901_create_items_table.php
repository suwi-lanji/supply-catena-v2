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
        Schema::create('item', function (Blueprint $table) {
            $table->id();
            $table->string('item_type');
            $table->string('image')->nullable();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('unit')->nullable();
            $table->boolean('returnable_item')->nullable();
            $table->double('selling_price');
            $table->unsignedBigInteger('sales_account_id')->nullable();
            $table->string('sales_description')->nullable();
            $table->double('cost_price');
            $table->unsignedBigInteger('purchase_account_id')->nullable();
            $table->string('purchases_description')->nullable();
            $table->boolean('track_inventory_for_this_item')->default(false);
            $table->string('inventory_account')->nullable();
            $table->unsignedBigInteger('opening_stock')->nullable();
            $table->double('opening_stock_rate_per_unit')->nullable();
            $table->unsignedBigInteger('reorder_level')->nullable();
            $table->unsignedBigInteger('preferred_vendor_id')->nullable();
            $table->string('dimensions')->nullable();
            $table->double('weight')->nullable();
            $table->unsignedBigInteger('manufucturer_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('upc')->nullable();
            $table->string('mpn')->nullable();
            $table->string('ean')->nullable();
            $table->string('isbn')->nullable();
            $table->unsignedBigInteger('team_id');
            $table->foreign('team_id')->references('id')
                ->on('teams')->onDelete('cascade');
            $table->foreign('sales_account_id')->references('id')
                ->on('sales_accounts')->onDelete('cascade');
            $table->foreign('purchase_account_id')->references('id')
                ->on('purchases_accounts')->onDelete('cascade');
            $table->foreign('preferred_vendor_id')->references('id')
                ->on('vendors')->onDelete('cascade');
            $table->foreign('manufucturer_id')->references('id')
                ->on('manufucturers')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')
                ->on('brands')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item');
    }
};
