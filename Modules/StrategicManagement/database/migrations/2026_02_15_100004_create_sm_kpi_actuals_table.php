<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sm_kpi_actuals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_id')->constrained('sm_kpis')->cascadeOnDelete();
            $table->string('period');
            $table->decimal('actual_value', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sm_kpi_actuals');
    }
};
