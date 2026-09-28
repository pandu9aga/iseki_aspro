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
        if (Schema::hasTable('list_reports') && !Schema::hasColumn('list_reports', 'Qr_Codes')) {
            Schema::table('list_reports', function (Blueprint $table) {
                $table->json('Qr_Codes')->nullable()->after('Auditor_Name');
            });
        }

        if (Schema::hasTable('list_trainings') && !Schema::hasColumn('list_trainings', 'Qr_Codes')) {
            Schema::table('list_trainings', function (Blueprint $table) {
                $table->json('Qr_Codes')->nullable()->after('Auditor_Name');
            });
        }

        if (Schema::hasTable('list_report_replacements') && !Schema::hasColumn('list_report_replacements', 'Qr_Codes')) {
            Schema::table('list_report_replacements', function (Blueprint $table) {
                $table->json('Qr_Codes')->nullable()->after('Auditor_Name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('list_reports') && Schema::hasColumn('list_reports', 'Qr_Codes')) {
            Schema::table('list_reports', function (Blueprint $table) {
                $table->dropColumn('Qr_Codes');
            });
        }

        if (Schema::hasTable('list_trainings') && Schema::hasColumn('list_trainings', 'Qr_Codes')) {
            Schema::table('list_trainings', function (Blueprint $table) {
                $table->dropColumn('Qr_Codes');
            });
        }

        if (Schema::hasTable('list_report_replacements') && Schema::hasColumn('list_report_replacements', 'Qr_Codes')) {
            Schema::table('list_report_replacements', function (Blueprint $table) {
                $table->dropColumn('Qr_Codes');
            });
        }
    }
};
