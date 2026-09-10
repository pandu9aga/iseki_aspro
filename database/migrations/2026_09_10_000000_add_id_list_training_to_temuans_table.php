<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('temuans', function (Blueprint $table) {
            $table->integer('Id_List_Report')->nullable()->change();
            $table->integer('Id_List_Training')->nullable()->after('Id_List_Report');
        });
    }

    public function down(): void
    {
        Schema::table('temuans', function (Blueprint $table) {
            $table->dropColumn('Id_List_Training');
            $table->integer('Id_List_Report')->nullable(false)->change();
        });
    }
};
