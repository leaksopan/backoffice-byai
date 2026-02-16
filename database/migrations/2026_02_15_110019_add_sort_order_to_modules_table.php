<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('modules', 'sort_order')) {
            Schema::table('modules', function (Blueprint $table): void {
                $table->integer('sort_order')->default(0);
            });
        }

        if (Schema::hasColumn('modules', 'sort')) {
            DB::table('modules')->update([
                'sort_order' => DB::raw('sort'),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('modules', 'sort_order')) {
            Schema::table('modules', function (Blueprint $table): void {
                $table->dropColumn('sort_order');
            });
        }
    }
};
