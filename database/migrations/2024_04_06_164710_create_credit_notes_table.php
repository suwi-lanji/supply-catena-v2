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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('credit_note_number');
            $table->string('reference_number');
            $table->date('credit_note_date');
            $table->unsignedBigInteger('sales_person_id')->nullable();
            $table->foreign('sales_person_id')->references('id')
                ->on('sales_persons')->onDelete('cascade');
            $table->string('subject')->nullable();
            $table->json('items');
            $table->double('discount')->default(0);
            $table->double('adjustment')->default(0);
            $table->double('total');
            $table->double('amount_due');
            $table->double('sub_total');
            $table->string('notes')->nullable();
            $table->boolean('is_draft')->default(false);
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')
                ->on('customers')->onDelete('cascade');
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
        Schema::dropIfExists('credit_notes');
    }
};
