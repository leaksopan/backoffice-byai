<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sm_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained('sm_goals')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('unit');
            $table->decimal('target_value', 15, 2);
            $table->decimal('baseline_value', 15, 2)->nullable();
            $table->string('formula')->nullable();
            $table->year('year');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sm_kpis');
    }
};
