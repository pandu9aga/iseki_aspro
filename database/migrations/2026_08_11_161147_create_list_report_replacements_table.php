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
        Schema::create('list_report_replacements', function (Blueprint $table) {
            $table->id('Id_List_Report_Replacement');
            $table->unsignedBigInteger('Id_Report_Replacement');
            $table->string('Name_Procedure');
            $table->string('Name_Area');
            $table->string('Name_Tractor');
            $table->text('Item_Procedure')->nullable();
            $table->timestamp('Time_List_Report')->nullable();
            $table->timestamp('Time_Approved_Leader')->nullable();
            $table->timestamp('Time_Approved_Auditor')->nullable();
            $table->string('Reporter_Name')->nullable();
            $table->string('Leader_Name')->nullable();
            $table->string('Auditor_Name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_report_replacements');
    }
};
