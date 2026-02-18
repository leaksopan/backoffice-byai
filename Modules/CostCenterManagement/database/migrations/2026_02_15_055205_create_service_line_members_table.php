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
        Schema::create('service_line_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_line_id')->constrained('service_lines')->onDelete('cascade');
            $table->foreignId('cost_center_id')->constrained('cost_centers')->onDelete('cascade');
            $table->decimal('allocation_percentage', 5, 2)->default(100.00);
            $table->timestamps();

            $table->unique(['service_line_id', 'cost_center_id'], 'unique_service_member');
            $table->index('service_line_id');
            $table->index('cost_center_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_line_members');
    }
};
