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
        Schema::create('report_replacements', function (Blueprint $table) {
            $table->id('Id_Report_Replacement');
            $table->unsignedBigInteger('Id_Report');
            $table->string('NIK_Replacement');
            $table->string('Name_Tractor');
            $table->string('Sequence_No_Plan')->nullable();
            $table->string('Production_Date_Plan')->nullable();
            $table->string('Type_Plan')->nullable();
            $table->unsignedBigInteger('Id_Report_Target')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_replacements');
    }
};
