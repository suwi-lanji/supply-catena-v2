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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number');
            $table->string('order_number');
            $table->date('bill_date');
            $table->date('due_date');
            $table->string('payment_terms');
            $table->string('subject')->nullable();
            $table->double('discount')->nullable();
            $table->string('notes')->nullable();
            $table->json('items');
            $table->double('adjustment')->default(0);
            $table->double('sub_total')->default(0);
            $table->string('discount_account')->nullable();
            $table->double('total')->default(0);
            $table->double('balance_due')->default(0);
            $table->boolean('received')->default(false);
            $table->boolean('payment_recorded')->default(false);
            $table->boolean('is_void')->default(false);
            $table->boolean('has_vendor_credits')->default(false);
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
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
        Schema::dropIfExists('bills');
    }
};
