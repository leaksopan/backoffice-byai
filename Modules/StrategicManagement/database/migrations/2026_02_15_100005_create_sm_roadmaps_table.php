<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sm_roadmaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained('sm_goals')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->year('year');
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->enum('status', ['planned', 'in_progress', 'done'])->default('planned');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sm_roadmaps');
    }
};
