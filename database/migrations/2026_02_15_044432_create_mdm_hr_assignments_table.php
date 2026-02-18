<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdm_hr_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_id')->constrained('mdm_human_resources')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('mdm_organization_units')->onDelete('restrict');
            $table->decimal('allocation_percentage', 5, 2);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('hr_id');
            $table->index('unit_id');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdm_hr_assignments');
    }
};
