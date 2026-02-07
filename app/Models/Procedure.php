<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Procedure extends Model
{
    protected $table = 'procedures';
    protected $primaryKey = 'Id_Procedure';
    public $timestamps = false;

    protected $fillable = [
        'Name_Procedure', 
        'Name_Area', 
        'Name_Tractor', 
        'Item_Procedure', 
        'Pic_Procedure'
    ];

    // Cast Pic_Procedure sebagai array
    protected $casts = [
        'Pic_Procedure' => 'array',
    ];

    // Relasi many-to-many dengan Member melalui JSON
    public function pics()
    {
        $picIds = $this->Pic_Procedure ?? [];
        if (empty($picIds)) {
            return collect();
        }
        return Member::whereIn('Id_Member', $picIds)->get();
    }

    // Helper method untuk mendapatkan nama-nama PIC
    public function getPicNamesAttribute()
    {
        return $this->pics()->pluck('Name_Member')->implode(', ');
    }

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($procedure) {
            $oldNameProcedure = $procedure->getOriginal('Name_Procedure');
            $oldNameArea = $procedure->getOriginal('Name_Area');
            $oldNameTractor = $procedure->getOriginal('Name_Tractor');

            $newNameProcedure = $procedure->Name_Procedure;
            $newNameArea = $procedure->Name_Area;
            $newNameTractor = $procedure->Name_Tractor;
            $newItemProcedure = $procedure->Item_Procedure;

            $matchingListReports = List_Report::where('Name_Procedure', $oldNameProcedure)
                ->where('Name_Area', $oldNameArea)
                ->where('Name_Tractor', $oldNameTractor)
                ->get();

            foreach ($matchingListReports as $listReport) {
                $isNotSubmitted =
                    is_null($listReport->Time_List_Report) &&
                    is_null($listReport->Time_Approved_Leader) &&
                    is_null($listReport->Time_Approved_Auditor);

                if ($isNotSubmitted) {
                    $listReport->update([
                        'Name_Procedure' => $newNameProcedure,
                        'Name_Area' => $newNameArea,
                        'Name_Tractor' => $newNameTractor,
                        'Item_Procedure' => $newItemProcedure,
                        'Time_List_Report' => null,
                        'Time_Approved_Leader' => null,
                        'Time_Approved_Auditor' => null,
                        'Leader_Name' => null,
                        'Auditor_Name' => null,
                    ]);

                    $report = $listReport->report;
                    if ($report) {
                        $startDate = \Carbon\Carbon::parse($report->Start_Report)->format('Y-m-d');
                        $targetDir = "reports/{$startDate}_{$report->Id_Member}";
                        $sourcePdfPath = "procedures/{$newNameTractor}/{$newNameArea}/{$newNameProcedure}.pdf";
                        $destPdfPath = "{$targetDir}/{$newNameProcedure}.pdf";

                        Storage::disk('public')->makeDirectory($targetDir, 0755, true, true);

                        if (Storage::disk('public')->exists($sourcePdfPath)) {
                            Storage::disk('public')->put(
                                $destPdfPath,
                                Storage::disk('public')->readStream($sourcePdfPath)
                            );
                        }
                    }
                }
            }
        });
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'Name_Area', 'Name_Area');
    }

    public function tractor()
    {
        return $this->belongsTo(Tractor::class, 'Name_Tractor', 'Name_Tractor');
    }
}