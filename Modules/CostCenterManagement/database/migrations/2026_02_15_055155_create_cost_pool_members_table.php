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
        Schema::create('cost_pool_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_pool_id')->constrained('cost_pools')->onDelete('cascade');
            $table->foreignId('cost_center_id')->constrained('cost_centers')->onDelete('cascade');
            $table->boolean('is_contributor')->default(true)->comment('TRUE = kontributor, FALSE = target');
            $table->timestamps();

            $table->unique(['cost_pool_id', 'cost_center_id'], 'unique_pool_member');
            $table->index('cost_pool_id');
            $table->index('cost_center_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_pool_members');
    }
};
