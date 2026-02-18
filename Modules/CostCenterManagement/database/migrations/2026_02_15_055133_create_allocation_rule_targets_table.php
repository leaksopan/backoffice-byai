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
        Schema::create('allocation_rule_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allocation_rule_id')->constrained('allocation_rules')->cascadeOnDelete();
            $table->foreignId('target_cost_center_id')->constrained('cost_centers')->restrictOnDelete();
            $table->decimal('allocation_percentage', 5, 2)->nullable();
            $table->decimal('allocation_weight', 10, 2)->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('allocation_rule_id');
            $table->index('target_cost_center_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocation_rule_targets');
    }
};
