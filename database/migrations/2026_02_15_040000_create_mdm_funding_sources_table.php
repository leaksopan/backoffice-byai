<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdm_funding_sources', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 255);
            $table->enum('type', [
                'apbn',
                'apbd_provinsi',
                'apbd_kab_kota',
                'pnbp',
                'hibah',
                'pinjaman',
                'lainnya'
            ]);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
            $table->index(['start_date', 'end_date'], 'idx_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdm_funding_sources');
    }
};
