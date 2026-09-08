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
        Schema::create('kpis', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->string('source')->nullable();
            $table->string('measurement_unit')->nullable();
            $table->string('measurement_direction')->nullable();
            $table->string('description', 255)->nullable();
            $table->string('formula')->nullable();
            $table->string('category')->nullable();
            $table->string('metric_category')->nullable();
            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->onDelete('cascade');
            $table->foreignId('sub_department_id')
                  ->nullable()
                  ->constrained('sub_departments')
                  ->nullOnDelete();
            $table->jsonb('threshold')->nullable();
            $table->float('weightage')->nullable();
            $table->string('min_interval')->nullable();
            $table->string('created_by', 64)->nullable();
            $table->string('updated_by', 64)->nullable();
            $table->timestamps();

            $table->index('department_id');
            $table->index('sub_department_id');
        });

        // Enforce enum values at database level (PostgreSQL compatible)
        DB::statement("
            ALTER TABLE kpis
            ADD CONSTRAINT chk_kpis_metric_category
            CHECK (metric_category IN ('Efficency', 'Health', 'Transformation'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpis');
    }
};
