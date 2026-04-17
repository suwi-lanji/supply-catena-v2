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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_type')->required();
            $table->string('salutation')->required();
            $table->string('first_name')->required();
            $table->string('last_name')->required();
            $table->string('company_name');
            $table->string('company_display_name');
            $table->string('email')->required();
            $table->string('phone')->required();
            $table->string('payment_terms')->required();
            $table->string('billing_country');
            $table->string('billing_province');
            $table->string('billing_city');
            $table->string('billing_phone');
            $table->string('billing_street_1');
            $table->string('billing_street_2');
            $table->string('shipping_country');
            $table->string('shipping_province');
            $table->string('shipping_city');
            $table->string('shipping_phone');
            $table->string('shipping_street_1');
            $table->string('shipping_street_2');
            $table->string('remarks');
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
        Schema::dropIfExists('customers');
    }
};
