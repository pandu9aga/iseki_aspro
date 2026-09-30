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
        foreach (['list_reports', 'list_trainings', 'list_report_replacements'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'Annotations')) {
                    $table->json('Annotations')->nullable()->after('Qr_Codes');
                }
                if (! Schema::hasColumn($tableName, 'Photos')) {
                    $table->json('Photos')->nullable()->after('Annotations');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['list_reports', 'list_trainings', 'list_report_replacements'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'Photos')) {
                    $table->dropColumn('Photos');
                }
                if (Schema::hasColumn($tableName, 'Annotations')) {
                    $table->dropColumn('Annotations');
                }
            });
        }
    }
};
