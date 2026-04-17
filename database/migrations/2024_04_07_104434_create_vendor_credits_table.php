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
        Schema::create('vendor_credits', function (Blueprint $table) {
            $table->id();
            $table->string('credit_note_number');
            $table->string('order_number');
            $table->date('vendor_credit_date');
            $table->string('subject')->nullable();
            $table->double('discount')->default(0);
            $table->double('adjustment')->default(0);
            $table->double('total');
            $table->double('amount_due');
            $table->double('sub_total');
            $table->string('notes')->nullable();
            $table->json('items');
            $table->boolean('is_draft')->default(false);
            $table->timestamps();
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->unsignedBigInteger('team_id');
            $table->foreign('team_id')->references('id')
                ->on('teams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_credits');
    }
};
