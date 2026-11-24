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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_academic_year_id');
            $table->unsignedBigInteger('target_academic_year_id');
            $table->unsignedBigInteger('source_section_id')->nullable();
            $table->unsignedBigInteger('target_section_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
