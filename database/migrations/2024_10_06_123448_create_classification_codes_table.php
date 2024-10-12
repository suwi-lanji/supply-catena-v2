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
        Schema::create('classification_codes', function (Blueprint $table) {
            $table->id();
            $table->string('itemClsCd')->unique(); // Item classification code (e.g., '15121513')
            $table->string('itemClsNm');           // Item classification name (e.g., 'Graphite lubricants')
            $table->integer('itemClsLvl');         // Classification level (e.g., 4)
            $table->string('taxTyCd')->nullable(); // Tax type code (nullable)
            $table->boolean('mjrTgYn')->nullable(); // Major tag (nullable, true/false)
            $table->boolean('useYn');              // Whether the classification is in use (Y/N)
            $table->timestamps();                  // Created at and updated at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classification_codes');
    }
};
