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
        Schema::create('kpi_archive_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('sub_department_id')->nullable();
            $table->unsignedBigInteger('kpi_id');
            $table->date('date')->nullable();
            $table->integer('value')->nullable();
            $table->date('previous_date')->nullable();
            $table->integer('previous_value')->nullable();
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
        Schema::dropIfExists('kpi_archive_snapshots');
    }
};
