<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class Procedure extends Model
{
    protected $table = 'procedures';
    protected $primaryKey = 'Id_Procedure';
    public $timestamps = false;

    protected $fillable = ['Name_Procedure', 'Name_Area', 'Name_Tractor', 'Item_Procedure'];

    // ====== Tambahkan event ini ======
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($procedure) {
            // 1. Cari semua List_Report yang cocok dengan kombinasi lama DAN baru?
            // Kita asumsikan bahwa sebelum update, data lama sudah tersimpan di $procedure->getOriginal()
            $oldNameProcedure = $procedure->getOriginal('Name_Procedure');
            $oldNameArea = $procedure->getOriginal('Name_Area');
            $oldNameTractor = $procedure->getOriginal('Name_Tractor');

            $newNameProcedure = $procedure->Name_Procedure;
            $newNameArea = $procedure->Name_Area;
            $newNameTractor = $procedure->Name_Tractor;
            $newItemProcedure = $procedure->Item_Procedure;

            // 2. Cari List_Report yang sebelumnya cocok dengan data lama
            $matchingListReports = List_Report::where('Name_Procedure', $oldNameProcedure)
                ->where('Name_Area', $oldNameArea)
                ->where('Name_Tractor', $oldNameTractor)
                ->get();

            foreach ($matchingListReports as $listReport) {
                // 3. Update ke nilai baru
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

                // 4. Ganti PDF di folder reports
                // Ambil data report terkait
                $report = $listReport->report;
                if ($report) {
                    $startReport = $report->Start_Report; // asumsi format: Y-m-d
                    $idMember = $report->Id_Member;

                    $oldPdfPath = "reports/{$startReport}_{$idMember}/{$oldNameProcedure}.pdf";
                    $newPdfPath = "reports/{$startReport}_{$idMember}/{$newNameProcedure}.pdf";
                    $sourcePdfPath = "procedures/{$newNameTractor}/{$newNameArea}/{$newNameProcedure}.pdf";

                    // Pastikan folder reports/{...} ada
                    Storage::disk('public')->makeDirectory(dirname($newPdfPath));

                    // Hapus file lama jika ada (opsional, tapi aman)
                    if (Storage::disk('public')->exists($oldPdfPath) && $oldPdfPath !== $newPdfPath) {
                        Storage::disk('public')->delete($oldPdfPath);
                    }

                    // Copy/replace dengan file terbaru dari procedures
                    if (Storage::disk('public')->exists($sourcePdfPath)) {
                        $pdfContent = Storage::disk('public')->get($sourcePdfPath);
                        Storage::disk('public')->put($newPdfPath, $pdfContent);
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
