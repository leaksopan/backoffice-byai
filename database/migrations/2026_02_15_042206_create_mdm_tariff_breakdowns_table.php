<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdm_tariff_breakdowns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tariff_id')->constrained('mdm_tariffs')->cascadeOnDelete();
            $table->enum('component_type', ['jasa_medis', 'jasa_sarana', 'bmhp', 'obat', 'administrasi']);
            $table->decimal('amount', 15, 2);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->timestamps();

            $table->index('tariff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdm_tariff_breakdowns');
    }
};
