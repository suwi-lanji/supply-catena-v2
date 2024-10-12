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
        Schema::create('item_groups', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('item_group_name');
            $table->string('description')->nullable();
            $table->boolean('returnable_item');
            $table->json('images');
            $table->string('unit');
            $table->json('attributes')->nullable();
            $table->unsignedBigInteger('sales_account_id');
            $table->unsignedBigInteger('purchases_account_id');
            $table->string('inventory_account');
            $table->foreign('sales_account_id')->references('id')
                ->on('sales_accounts')->onDelete('cascade');
            $table->foreign('purchases_account_id')->references('id')
                ->on('purchases_accounts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_groups');
    }
};
