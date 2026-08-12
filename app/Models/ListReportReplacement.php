<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListReportReplacement extends Model
{
    protected $table = 'list_report_replacements';

    protected $primaryKey = 'Id_List_Report_Replacement';

    protected $fillable = [
        'Id_Report_Replacement',
        'Name_Procedure',
        'Name_Area',
        'Name_Tractor',
        'Item_Procedure',
        'Time_List_Report',
        'Time_Approved_Leader',
        'Time_Approved_Auditor',
        'Reporter_Name',
        'Leader_Name',
        'Auditor_Name',
    ];

    public function reportReplacement()
    {
        return $this->belongsTo(ReportReplacement::class, 'Id_Report_Replacement', 'Id_Report_Replacement');
    }

    public function getDisplayNameAttribute(): string
    {
        $mower = $this->Model_Mower_Plan ? ' - '.$this->Model_Mower_Plan : '';
        $collector = $this->Model_Collector_Plan ? ' - '.$this->Model_Collector_Plan : '';

        return sprintf('%s%s%s', $this->Name_Procedure, $mower, $collector);
    }
}
