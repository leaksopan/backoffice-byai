<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sm_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vision_id')->constrained('sm_visions')->cascadeOnDelete();
            $table->year('year');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sm_evaluations');
    }
};
