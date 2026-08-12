<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportReplacement extends Model
{
    protected $table = 'report_replacements';

    protected $primaryKey = 'Id_Report_Replacement';

    protected $fillable = [
        'Id_Report',
        'NIK_Replacement',
        'Name_Tractor',
        'Sequence_No_Plan',
        'Production_Date_Plan',
        'Type_Plan',
        'Id_Report_Target',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class, 'Id_Report', 'Id_Report');
    }

    public function listReportReplacements()
    {
        return $this->hasMany(ListReportReplacement::class, 'Id_Report_Replacement', 'Id_Report_Replacement');
    }
}
