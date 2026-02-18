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
        Schema::create('cost_center_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_center_id')->constrained('cost_centers')->onDelete('restrict');
            $table->date('transaction_date');
            $table->enum('transaction_type', ['direct_cost', 'allocated_cost', 'revenue']);
            $table->enum('category', ['personnel', 'supplies', 'services', 'depreciation', 'overhead', 'other']);
            $table->decimal('amount', 15, 2);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index('cost_center_id');
            $table->index('transaction_date');
            $table->index('transaction_type');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_center_transactions');
    }
};
