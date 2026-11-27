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
        Schema::table('sections', function (Blueprint $table) {
            $table->unique(['academic_year_id', 'grade', 'name']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->date('tmt_jabatan')->nullable()->comment('Tanggal Mulai Tugas Jabatan')->change();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->unique('nisn');
            $table->date('ayah_tanggal_lahir')->nullable()->change();
            $table->date('ibu_tanggal_lahir')->nullable()->change();
            $table->date('wali_tanggal_lahir')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['nisn']);
            $table->string('ayah_tanggal_lahir')->nullable()->change();
            $table->string('ibu_tanggal_lahir')->nullable()->change();
            $table->string('wali_tanggal_lahir')->nullable()->change();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('tmt_jabatan')->nullable()->comment('Tanggal Mulai Tugas Jabatan')->change();
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropUnique(['academic_year_id', 'grade', 'name']);
        });
    }
};