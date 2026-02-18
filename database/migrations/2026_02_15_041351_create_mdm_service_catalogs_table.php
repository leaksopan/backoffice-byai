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
        Schema::create('mdm_service_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->enum('category', [
                'rawat_jalan',
                'rawat_inap',
                'igd',
                'penunjang_medis',
                'tindakan',
                'operasi',
                'persalinan',
                'administrasi'
            ]);
            $table->foreignId('unit_id')->constrained('mdm_organization_units')->restrictOnDelete();
            $table->string('inacbg_code', 50)->nullable();
            $table->integer('standard_duration')->nullable()->comment('dalam menit');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('category');
            $table->index('unit_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mdm_service_catalogs');
    }
};
