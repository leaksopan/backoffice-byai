<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cost_center_audit_logs')) {
            return;
        }
        
        Schema::create('cost_center_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type', 100); // Model class name
            $table->unsignedBigInteger('auditable_id'); // Model ID
            $table->string('event', 50); // created, updated, deleted, approved, etc
            $table->json('old_values')->nullable(); // Data sebelum perubahan
            $table->json('new_values')->nullable(); // Data setelah perubahan
            $table->text('justification')->nullable(); // Alasan perubahan
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('event');
            $table->index('user_id');
            $table->index('created_at');
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_center_audit_logs');
    }
};
