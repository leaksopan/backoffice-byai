<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdm_human_resources', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 50)->unique();
            $table->string('name', 255);
            $table->enum('category', [
                'medis_dokter',
                'medis_perawat',
                'medis_bidan',
                'penunjang_medis',
                'administrasi',
                'umum'
            ]);
            $table->string('position', 100);
            $table->enum('employment_status', ['pns', 'pppk', 'kontrak', 'honorer']);
            $table->string('grade', 10)->nullable();
            $table->decimal('basic_salary', 15, 2)->nullable();
            $table->integer('effective_hours_per_week')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdm_human_resources');
    }
};
