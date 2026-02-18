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
        Schema::create('cost_center_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_center_id')->constrained('cost_centers')->onDelete('cascade');
            $table->integer('fiscal_year');
            $table->integer('period_month'); // 1-12
            $table->enum('category', ['personnel', 'supplies', 'services', 'depreciation', 'overhead', 'other']);
            $table->decimal('budget_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->decimal('variance_amount', 15, 2)->default(0);
            $table->decimal('utilization_percentage', 5, 2)->default(0);
            $table->integer('revision_number')->default(0);
            $table->text('revision_justification')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Unique constraint untuk budget per periode per category per revision
            $table->unique(
                ['cost_center_id', 'fiscal_year', 'period_month', 'category', 'revision_number'],
                'unique_budget_period'
            );

            // Indexes untuk query performance
            $table->index('cost_center_id');
            $table->index(['fiscal_year', 'period_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_center_budgets');
    }
};
