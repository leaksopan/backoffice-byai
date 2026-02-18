<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdm_tariffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('mdm_service_catalogs')->restrictOnDelete();
            $table->enum('service_class', ['vip', 'kelas_1', 'kelas_2', 'kelas_3', 'umum']);
            $table->decimal('tariff_amount', 15, 2);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('payer_type', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['service_id', 'service_class']);
            $table->index(['start_date', 'end_date']);
            $table->index('is_active');
            $table->unique(['service_id', 'service_class', 'payer_type', 'start_date', 'end_date'], 'unique_tariff_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdm_tariffs');
    }
};
