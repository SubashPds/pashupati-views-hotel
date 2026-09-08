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
        Schema::create('kpi_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->onDelete('cascade');
            $table->foreignId('sub_department_id')
                  ->nullable()
                  ->constrained('sub_departments')
                  ->nullOnDelete();
            $table->foreignId('kpi_id')
                  ->constrained('kpis')
                  ->onDelete('cascade');
            $table->date('date')->nullable();
            $table->integer('value')->nullable();
            $table->date('previous_date')->nullable();
            $table->integer('previous_value')->nullable();
            $table->jsonb('raw_data')->nullable();
            $table->timestamps();

            $table->index(['department_id', 'kpi_id', 'date']);
            $table->index('sub_department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_snapshots');
    }
};
