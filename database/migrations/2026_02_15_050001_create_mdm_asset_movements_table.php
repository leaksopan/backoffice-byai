<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdm_asset_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('mdm_assets')->cascadeOnDelete();
            $table->foreignId('from_location_id')->nullable()->constrained('mdm_organization_units')->nullOnDelete();
            $table->foreignId('to_location_id')->constrained('mdm_organization_units')->restrictOnDelete();
            $table->date('movement_date');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('asset_id');
            $table->index('movement_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdm_asset_movements');
    }
};
