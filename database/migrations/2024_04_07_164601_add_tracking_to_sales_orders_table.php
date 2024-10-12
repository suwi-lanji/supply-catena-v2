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
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->boolean('invoiced')->default(false);
            $table->boolean('packaged')->default(false);
            $table->boolean('payed')->default(false);
            $table->boolean('shipped')->default(false);
            $table->boolean('email_sent')->default(false);
            $table->double('adjustment')->default(0)->change();
            $table->double('shipment_charges')->default(0)->change();
            $table->double('discount')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            //
        });
    }
};
