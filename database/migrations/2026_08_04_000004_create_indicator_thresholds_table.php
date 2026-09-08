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
        Schema::create('indicator_thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->onDelete('cascade');
            $table->foreignId('sub_department_id')
                  ->nullable()
                  ->constrained('sub_departments')
                  ->nullOnDelete();
            $table->foreignId('indicator_id')
                  ->constrained('indicators')
                  ->onDelete('cascade');
            $table->string('type')->nullable();
            $table->jsonb('threshold')->nullable();
            $table->string('created_by', 64)->nullable();
            $table->string('updated_by', 64)->nullable();
            $table->timestamps();

            // Indexes for common lookup patterns
            $table->index('department_id');
            $table->index('sub_department_id');
            $table->index('indicator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indicator_thresholds');
    }
};
