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
        Schema::create('allocation_journals', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id', 50)->index();
            $table->foreignId('allocation_rule_id')->nullable()->constrained('allocation_rules')->restrictOnDelete();
            $table->foreignId('source_cost_center_id')->constrained('cost_centers')->restrictOnDelete();
            $table->foreignId('target_cost_center_id')->constrained('cost_centers')->restrictOnDelete();
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            $table->decimal('source_amount', 15, 2);
            $table->decimal('allocated_amount', 15, 2);
            $table->decimal('allocation_base_value', 15, 2)->nullable();
            $table->text('calculation_detail')->nullable();
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft')->index();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Indexes
            $table->index(['period_start', 'period_end']);
            $table->index('source_cost_center_id');
            $table->index('target_cost_center_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocation_journals');
    }
};
