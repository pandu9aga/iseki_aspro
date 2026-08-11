<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->integer('Id_Training', true);
            $table->timestamp('Start_Training')->nullable();
            $table->string('Name_Training', 255)->nullable();
            $table->integer('Id_Member')->nullable();
        });

        Schema::create('list_trainings', function (Blueprint $table) {
            $table->integer('Id_List_Training', true);
            $table->integer('Id_Training');
            $table->string('Name_Procedure', 100);
            $table->string('Name_Area', 100);
            $table->string('Name_Tractor', 100);
            $table->text('Item_Procedure')->nullable();
            $table->timestamp('Time_List_Report')->nullable();
            $table->timestamp('Time_Approved_Leader')->nullable();
            $table->timestamp('Time_Approved_Auditor')->nullable();
            $table->string('Reporter_Name', 100)->nullable();
            $table->string('Leader_Name', 100)->nullable();
            $table->string('Auditor_Name', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('list_trainings');
        Schema::dropIfExists('trainings');
    }
};
