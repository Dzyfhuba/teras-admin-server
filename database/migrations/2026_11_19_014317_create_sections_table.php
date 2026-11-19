<?php

use App\Models\AcademicYear;
use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AcademicYear::class)->constrained()->cascadeOnDelete();
            $table->smallInteger('grade')->comment('1, 2, 3, 4, 5, 6');
            $table->string('name')->comment('Al Fajr, Ad Dhuha, ddl. tanpa angka, grade sudah ada kolomnya');
            $table->foreignIdFor(Employee::class, 'wali_kelas_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
