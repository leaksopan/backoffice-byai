<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdm_assets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->enum('category', [
                'tanah',
                'gedung',
                'peralatan_medis',
                'peralatan_non_medis',
                'kendaraan',
                'inventaris'
            ]);
            $table->decimal('acquisition_value', 15, 2);
            $table->date('acquisition_date');
            $table->integer('useful_life_years')->nullable();
            $table->enum('depreciation_method', [
                'straight_line',
                'declining_balance',
                'units_of_production'
            ])->nullable();
            $table->decimal('residual_value', 15, 2)->default(0);
            $table->foreignId('current_location_id')->nullable()->constrained('mdm_organization_units')->nullOnDelete();
            $table->enum('condition', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('current_location_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdm_assets');
    }
};
