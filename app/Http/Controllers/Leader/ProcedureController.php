<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Member;
use App\Models\Procedure;
use App\Models\Tractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcedureController extends Controller
{
    public function index()
    {
        $page = 'procedure';
        $tractors = Tractor::orderBy('Name_Tractor', 'asc')->get();

        // Hitung jumlah procedure untuk setiap tractor
        $tractorProcedureCounts = [];
        foreach ($tractors as $tractor) {
            $tractorProcedureCounts[$tractor->Name_Tractor] = Procedure::where('Name_Tractor', $tractor->Name_Tractor)->count();
        }

        return view('leaders.procedures.index', compact('page', 'tractors', 'tractorProcedureCounts'));
    }

    public function create_tractor(Request $request)
    {
        $request->validate([
            'Name_Tractor' => 'required|unique:tractors,Name_Tractor',
            'Photo_Tractor' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'Name_Tractor.required' => 'Nama wajib diisi',
            'Photo_Tractor.required' => 'Foto wajib diunggah',
            'Photo_Tractor.image' => 'File harus berupa gambar',
            'Photo_Tractor.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp',
            'Photo_Tractor.max' => 'Ukuran maksimal gambar adalah 2MB',
        ]);

        $name = $request->input('Name_Tractor');
        $photoPath = null;

        if ($request->hasFile('Photo_Tractor')) {
            $file = $request->file('Photo_Tractor');
            $filename = uniqid('tractor_').'.'.$file->getClientOriginalExtension();
            $photoPath = 'storage/tractors/'.$filename;
            $file->move(public_path('storage/tractors'), $filename);
        }

        DB::table('tractors')->insert([
            'Name_Tractor' => $name,
            'Photo_Tractor' => $photoPath,
        ]);

        Storage::disk('public')->makeDirectory('procedures/'.$name);

        return redirect()->route('procedure')->with('success', 'Tractor berhasil ditambahkan dan foto disimpan');
    }

    public function update_tractor(Request $request, string $Id_Tractor)
    {
        $oldTractor = DB::table('tractors')->where('Id_Tractor', $Id_Tractor)->first();
        $oldName = $oldTractor->Name_Tractor;
        $oldPhoto = $oldTractor->Photo_Tractor;

        $request->validate([
            'Name_Tractor' => 'required|unique:tractors,Name_Tractor,'.$Id_Tractor.',Id_Tractor',
        ], [
            'Name_Tractor.required' => 'Nama wajib diisi',
        ]);

        $newName = $request->input('Name_Tractor');
        $photoPath = $oldPhoto;

        if ($request->hasFile('Photo_Tractor')) {
            $file = $request->file('Photo_Tractor');

            if ($oldPhoto && $oldPhoto !== 'storage/tractors/default.png' && Storage::disk('public')->exists(str_replace('storage/', '', $oldPhoto))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $oldPhoto));
            }

            $fileName = uniqid('tractor_').'.'.$file->getClientOriginalExtension();
            $file->move(public_path('storage/tractors'), $fileName);
            $photoPath = 'storage/tractors/'.$fileName;
        }

        DB::table('tractors')->where('Id_Tractor', $Id_Tractor)->update([
            'Name_Tractor' => $newName,
            'Photo_Tractor' => $photoPath,
        ]);

        if ($oldName !== $newName) {
            $oldFolder = 'procedures/'.$oldName;
            $newFolder = 'procedures/'.$newName;

            if (Storage::disk('public')->exists($oldFolder)) {
                Storage::disk('public')->move($oldFolder, $newFolder);
            }

            DB::table('areas')->where('Name_Tractor', $oldName)->update(['Name_Tractor' => $newName]);
            DB::table('procedures')->where('Name_Tractor', $oldName)->update(['Name_Tractor' => $newName]);
        }

        return redirect()->route('procedure')->with('success', 'Data dan folder berhasil diedit');
    }

    public function destroy_tractor($Id_Tractor)
    {
        $tractor = Tractor::findOrFail($Id_Tractor);
        $nameTractor = $tractor->Name_Tractor;
        $folderName = 'procedures/'.$nameTractor;

        DB::table('areas')->where('Name_Tractor', $nameTractor)->delete();
        DB::table('procedures')->where('Name_Tractor', $nameTractor)->delete();
        $tractor->delete();

        if (Storage::disk('public')->exists($folderName)) {
            Storage::disk('public')->deleteDirectory($folderName);
        }

        return redirect()->route('procedure')->with('success', 'Data dan folder berhasil dihapus');
    }

    public function index_area($Name_Tractor)
    {
        $page = 'procedure';
        $tractor = $Name_Tractor;
        $photoTractor = Tractor::where('Name_Tractor', $Name_Tractor)->value('Photo_Tractor');
        $areas = Area::where('Name_Tractor', $Name_Tractor)->orderBy('Name_Area', 'asc')->get();

        return view('leaders.procedures.areas', compact('page', 'tractor', 'photoTractor', 'areas'));
    }

    public function create_area(Request $request)
    {
        $request->validate([
            'Name_Tractor' => 'required',
            'Name_Area' => 'required',
        ], [
            'Name_Tractor.required' => 'Nama tractor wajib diisi',
            'Name_Area.required' => 'Nama area wajib diisi',
        ]);

        $Name_Tractor = $request->input('Name_Tractor');
        $Name_Area = $request->input('Name_Area');

        if (DB::table('areas')->where('Name_Tractor', $Name_Tractor)->where('Name_Area', $Name_Area)->exists()) {
            return back()->withErrors(['Nama area di tractor ini sudah ada'])->withInput();
        }

        DB::table('areas')->insert([
            'Name_Tractor' => $Name_Tractor,
            'Name_Area' => $Name_Area,
        ]);

        Storage::disk('public')->makeDirectory("procedures/$Name_Tractor/$Name_Area");

        return redirect()
            ->route('procedure.area.index', ['Name_Tractor' => $Name_Tractor])
            ->with('success', 'Area berhasil ditambahkan dan folder dibuat');
    }

    public function update_area(Request $request, string $Id_Area)
    {
        $oldArea = DB::table('areas')->where('Id_Area', $Id_Area)->first();
        if (! $oldArea) {
            return back()->withErrors(['Area tidak ditemukan']);
        }

        $oldNameTractor = $oldArea->Name_Tractor;
        $oldNameArea = $oldArea->Name_Area;

        $request->validate([
            'Name_Tractor' => 'required',
            'Name_Area' => 'required',
        ], [
            'Name_Tractor.required' => 'Nama tractor wajib diisi',
            'Name_Area.required' => 'Nama area wajib diisi',
        ]);

        $newNameTractor = $request->input('Name_Tractor');
        $newNameArea = $request->input('Name_Area');

        if (DB::table('areas')
            ->where('Name_Tractor', $newNameTractor)
            ->where('Name_Area', $newNameArea)
            ->where('Id_Area', '!=', $Id_Area)
            ->exists()
        ) {
            return back()->withErrors(['Nama area di tractor ini sudah ada'])->withInput();
        }

        DB::table('areas')->where('Id_Area', $Id_Area)->update([
            'Name_Tractor' => $newNameTractor,
            'Name_Area' => $newNameArea,
        ]);

        if ($oldNameTractor !== $newNameTractor || $oldNameArea !== $newNameArea) {
            $oldFolder = "procedures/$oldNameTractor/$oldNameArea";
            $newFolder = "procedures/$newNameTractor/$newNameArea";

            if (Storage::disk('public')->exists($oldFolder)) {
                Storage::disk('public')->makeDirectory("procedures/$newNameTractor");
                Storage::disk('public')->move($oldFolder, $newFolder);

                $oldParent = "procedures/$oldNameTractor";
                if (
                    empty(Storage::disk('public')->allFiles($oldParent)) &&
                    empty(Storage::disk('public')->allDirectories($oldParent))
                ) {
                    Storage::disk('public')->deleteDirectory($oldParent);
                }

                DB::table('procedures')
                    ->where('Name_Tractor', $oldNameTractor)
                    ->where('Name_Area', $oldNameArea)
                    ->update(['Name_Area' => $newNameArea]);
            }
        }

        return redirect()->route('procedure.area.index', ['Name_Tractor' => $newNameTractor])
            ->with('success', 'Data dan folder berhasil diedit');
    }

    public function destroy_area($Id_Area)
    {
        $area = Area::findOrFail($Id_Area);
        $Name_Tractor = $area->Name_Tractor;
        $Name_Area = $area->Name_Area;
        $folderName = 'procedures/'.$Name_Tractor.'/'.$Name_Area;

        DB::table('procedures')
            ->where('Name_Tractor', $Name_Tractor)
            ->where('Name_Area', $Name_Area)
            ->delete();

        $area->delete();

        if (Storage::disk('public')->exists($folderName)) {
            Storage::disk('public')->deleteDirectory($folderName);
        }

        return redirect()->route('procedure.area.index', ['Name_Tractor' => $Name_Tractor])
            ->with('success', 'Data dan folder berhasil dihapus');
    }

    public function index_procedure($Name_Tractor, $Name_Area)
    {
        $page = 'procedure';
        $tractor = $Name_Tractor;
        $photoTractor = Tractor::where('Name_Tractor', $Name_Tractor)->value('Photo_Tractor');
        $area = $Name_Area;
        $procedures = Procedure::where('Name_Tractor', $Name_Tractor)
            ->where('Name_Area', $Name_Area)
            ->orderBy('Name_Procedure', 'asc')
            ->get();

        // Tambahkan ini untuk mendapatkan semua member
        $members = Member::orderBy('Name_Member', 'asc')->get();

        return view('leaders.procedures.procedures', compact('page', 'tractor', 'photoTractor', 'area', 'procedures', 'members'));
    }

    public function create_procedure(Request $request)
    {
        $request->validate([
            'File_Procedure.*' => 'required|mimes:pdf',
            'Name_Tractor' => 'required',
            'Name_Area' => 'required',
        ]);

        $tractor = $request->Name_Tractor;
        $area = $request->Name_Area;

        if ($request->hasFile('File_Procedure')) {
            foreach ($request->file('File_Procedure') as $file) {
                $originalName = $file->getClientOriginalName();
                $nameProcedure = pathinfo($originalName, PATHINFO_FILENAME);
                $filename = $originalName;
                $path = 'procedures/'.$tractor.'/'.$area;

                // Simpan file PDF versi baru
                $file->storeAs($path, $filename, 'public');

                // Pertahankan Item_Procedure di tabel procedures
                $existingProcedure = \App\Models\Procedure::where([
                    'Name_Tractor' => $tractor,
                    'Name_Area' => $area,
                    'Name_Procedure' => $nameProcedure,
                ])->first();

                $itemProcedureValue = $existingProcedure ? $existingProcedure->Item_Procedure : '';

                DB::table('procedures')->updateOrInsert(
                    [
                        'Name_Tractor' => $tractor,
                        'Name_Area' => $area,
                        'Name_Procedure' => $nameProcedure,
                    ],
                    [
                        'Item_Procedure' => $itemProcedureValue,
                    ]
                );

                // 🔁 Sinkronisasi ke List_Report — hanya jika belum dikirim
                $matchingListReports = \App\Models\List_Report::where([
                    'Name_Tractor' => $tractor,
                    'Name_Area' => $area,
                    'Name_Procedure' => $nameProcedure,
                ])->get();

                foreach ($matchingListReports as $listReport) {
                    // ✅ Cek: apakah laporan belum pernah dikirim?
                    $isNotSubmitted =
                        is_null($listReport->Time_List_Report) &&
                        is_null($listReport->Time_Approved_Leader) &&
                        is_null($listReport->Time_Approved_Auditor);

                    if ($isNotSubmitted) {
                        // Ambil Item_Procedure lama (jangan reset jadi kosong)
                        $oldItemProcedure = $listReport->Item_Procedure ?? '';

                        // Reset hanya jika belum dikirim
                        DB::table('list_reports')
                            ->where('Id_List_Report', $listReport->Id_List_Report)
                            ->update([
                                'Item_Procedure' => $oldItemProcedure,
                                'Time_List_Report' => null,
                                'Time_Approved_Leader' => null,
                                'Time_Approved_Auditor' => null,
                                'Leader_Name' => null,
                                'Auditor_Name' => null,
                            ]);

                        // ✅ Timpa file PDF di folder report
                        $reportFolder = $listReport->report;
                        if ($reportFolder) {
                            $startDate = \Carbon\Carbon::parse($reportFolder->Start_Report)->format('Y-m-d');
                            $targetFolder = "reports/{$startDate}_{$reportFolder->Id_Member}";
                            Storage::disk('public')->makeDirectory($targetFolder, 0755, true, true);

                            $sourcePdf = "procedures/{$tractor}/{$area}/{$listReport->Name_Procedure}.pdf";
                            $destPdf = "{$targetFolder}/{$listReport->Name_Procedure}.pdf";

                            if (Storage::disk('public')->exists($sourcePdf)) {
                                Storage::disk('public')->put($destPdf, Storage::disk('public')->readStream($sourcePdf));
                            }
                        }
                    }
                    // Jika sudah dikirim → lewati (tidak update apa-apa)
                }
            }
        }

        return redirect()->route('procedure.procedure.index', [
            'Name_Tractor' => $tractor,
            'Name_Area' => $area,
        ])->with('success', 'Prosedur berhasil ditambahkan atau diperbarui');
    }

    public function update_procedure(Request $request, string $Id_Procedure)
    {
        $oldProcedure = DB::table('procedures')->where('Id_Procedure', $Id_Procedure)->first();
        if (! $oldProcedure) {
            return back()->withErrors(['Procedure tidak ditemukan']);
        }

        $oldNameTractor = $oldProcedure->Name_Tractor;
        $oldNameArea = $oldProcedure->Name_Area;
        $oldNameProcedure = $oldProcedure->Name_Procedure;

        $request->validate([
            'Name_Tractor' => 'required',
            'Name_Area' => 'required',
            'Name_Procedure' => 'required',
        ], [
            'Name_Tractor.required' => 'Nama tractor wajib diisi',
            'Name_Area.required' => 'Nama area wajib diisi',
            'Name_Procedure.required' => 'Nama procedure wajib diisi',
        ]);

        $newNameTractor = $request->input('Name_Tractor');
        $newNameArea = $request->input('Name_Area');
        $newNameProcedure = $request->input('Name_Procedure');
        $newItemProcedure = $request->input('Item_Procedure') ?? '';

        // Cek duplikat (kecuali diri sendiri)
        if (DB::table('procedures')
            ->where('Name_Tractor', $newNameTractor)
            ->where('Name_Area', $newNameArea)
            ->where('Name_Procedure', $newNameProcedure)
            ->where('Id_Procedure', '!=', $Id_Procedure)
            ->exists()
        ) {
            return back()->withErrors(['Nama procedure di area tractor ini sudah ada'])->withInput();
        }

        // Update data procedure
        DB::table('procedures')->where('Id_Procedure', $Id_Procedure)->update([
            'Name_Tractor' => $newNameTractor,
            'Name_Area' => $newNameArea,
            'Name_Procedure' => $newNameProcedure,
            'Item_Procedure' => $newItemProcedure,
        ]);

        // Rename file jika ada perubahan nama
        if (
            $oldNameTractor !== $newNameTractor ||
            $oldNameArea !== $newNameArea ||
            $oldNameProcedure !== $newNameProcedure
        ) {
            $oldPath = "procedures/{$oldNameTractor}/{$oldNameArea}/{$oldNameProcedure}.pdf";
            $newDir = "procedures/{$newNameTractor}/{$newNameArea}";
            $newPath = "{$newDir}/{$newNameProcedure}.pdf";

            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->makeDirectory($newDir, 0755, true, true);
                Storage::disk('public')->move($oldPath, $newPath);
            }
        }

        // 🔁 Sinkronisasi ke List_Report — hanya jika belum dikirim
        $matchingListReports = \App\Models\List_Report::where([
            'Name_Tractor' => $oldNameTractor,
            'Name_Area' => $oldNameArea,
            'Name_Procedure' => $oldNameProcedure,
        ])->get();

        foreach ($matchingListReports as $listReport) {
            // ✅ Cek: apakah laporan BELUM PERNAH dikirim?
            $isNotSubmitted =
                is_null($listReport->Time_List_Report) &&
                is_null($listReport->Time_Approved_Leader) &&
                is_null($listReport->Time_Approved_Auditor);

            if ($isNotSubmitted) {
                // ✅ Gunakan Item_Procedure BARU (dari input)
                DB::table('list_reports')
                    ->where('Id_List_Report', $listReport->Id_List_Report)
                    ->update([
                        'Name_Tractor' => $newNameTractor,
                        'Name_Area' => $newNameArea,
                        'Name_Procedure' => $newNameProcedure,
                        'Item_Procedure' => $newItemProcedure, // ← nilai baru

                        // Reset status
                        'Time_List_Report' => null,
                        'Time_Approved_Leader' => null,
                        'Time_Approved_Auditor' => null,
                        'Leader_Name' => null,
                        'Auditor_Name' => null,
                    ]);

                // ✅ Timpa file PDF di folder report dengan versi terbaru
                $report = $listReport->report;
                if ($report) {
                    $startDate = \Carbon\Carbon::parse($report->Start_Report)->format('Y-m-d');
                    $targetDir = "reports/{$startDate}_{$report->Id_Member}";
                    $sourcePdfPath = "procedures/{$newNameTractor}/{$newNameArea}/{$newNameProcedure}.pdf";
                    $destPdfPath = "{$targetDir}/{$newNameProcedure}.pdf";

                    Storage::disk('public')->makeDirectory($targetDir, 0755, true, true);

                    if (Storage::disk('public')->exists($sourcePdfPath)) {
                        // Gunakan put + readStream untuk menimpa dengan andal
                        Storage::disk('public')->put(
                            $destPdfPath,
                            Storage::disk('public')->readStream($sourcePdfPath)
                        );
                    }
                }
            }
            // ❌ Jika sudah dikirim → jangan update apa pun (abaikan)
        }

        return redirect()
            ->route('procedure.procedure.index', [
                'Name_Tractor' => $newNameTractor,
                'Name_Area' => $newNameArea,
            ])
            ->with('success', 'Data dan file berhasil diedit');
    }

    public function upload_procedure(Request $request, string $Id_Procedure)
    {
        $request->validate([
            'File_Procedure' => 'required|mimes:pdf',
        ]);

        $procedure = DB::table('procedures')->where('Id_Procedure', $Id_Procedure)->first();
        if (! $procedure) {
            return back()->withErrors(['Procedure tidak ditemukan']);
        }

        $nameTractor = $procedure->Name_Tractor;
        $nameArea = $procedure->Name_Area;
        $nameProcedure = $procedure->Name_Procedure;

        $folderPath = 'procedures/'.$nameTractor.'/'.$nameArea;
        $fileName = $nameProcedure.'.pdf';

        // Simpan file PDF baru
        $file = $request->file('File_Procedure');
        Storage::disk('public')->putFileAs($folderPath, $file, $fileName);

        // 🔁 Sinkronisasi ke List_Report — hanya jika belum pernah dikirim
        $matchingListReports = \App\Models\List_Report::where([
            'Name_Procedure' => $nameProcedure,
            'Name_Area' => $nameArea,
            'Name_Tractor' => $nameTractor,
        ])->get();

        foreach ($matchingListReports as $listReport) {
            // ✅ Cek: apakah laporan BELUM PERNAH dikirim?
            $isNotSubmitted =
                is_null($listReport->Time_List_Report) &&
                is_null($listReport->Time_Approved_Leader) &&
                is_null($listReport->Time_Approved_Auditor);

            if ($isNotSubmitted) {
                // Gunakan Item_Procedure dari data prosedur (tidak diubah, karena ini hanya upload file)
                $itemProcedureValue = $procedure->Item_Procedure ?? '';

                // Reset status, pertahankan Item_Procedure
                DB::table('list_reports')
                    ->where('Id_List_Report', $listReport->Id_List_Report)
                    ->update([
                        'Item_Procedure' => $itemProcedureValue,
                        'Time_List_Report' => null,
                        'Time_Approved_Leader' => null,
                        'Time_Approved_Auditor' => null,
                        'Leader_Name' => null,
                        'Auditor_Name' => null,
                    ]);

                // ✅ Timpa file PDF di folder report dengan versi terbaru
                $report = $listReport->report;
                if ($report) {
                    $startDate = \Carbon\Carbon::parse($report->Start_Report)->format('Y-m-d');
                    $targetDir = "reports/{$startDate}_{$report->Id_Member}";
                    $sourcePdfPath = "procedures/{$nameTractor}/{$nameArea}/{$nameProcedure}.pdf";
                    $destPdfPath = "{$targetDir}/{$nameProcedure}.pdf";

                    Storage::disk('public')->makeDirectory($targetDir, 0755, true, true);

                    if (Storage::disk('public')->exists($sourcePdfPath)) {
                        // Gunakan put + readStream untuk menimpa file yang mungkin sedang digunakan
                        Storage::disk('public')->put(
                            $destPdfPath,
                            Storage::disk('public')->readStream($sourcePdfPath)
                        );
                    }
                }
            }
            // ❌ Jika laporan sudah dikirim (ada approval), jangan lakukan apa-apa
        }

        return redirect()
            ->route('procedure.procedure.index', [
                'Name_Tractor' => $nameTractor,
                'Name_Area' => $nameArea,
            ])
            ->with('success', 'File procedure berhasil diperbarui');
    }

    public function destroy_procedure($Id_Procedure)
    {
        $procedure = Procedure::findOrFail($Id_Procedure);
        $Name_Tractor = $procedure->Name_Tractor;
        $Name_Area = $procedure->Name_Area;
        $Name_Procedure = $procedure->Name_Procedure;
        $filePath = 'procedures/'.$Name_Tractor.'/'.$Name_Area.'/'.$Name_Procedure.'.pdf';

        $procedure->delete();

        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        return redirect()
            ->route('procedure.procedure.index', [
                'Name_Tractor' => $Name_Tractor,
                'Name_Area' => $Name_Area,
            ])
            ->with('success', 'File procedure berhasil dihapus');
    }

    public function insert_item_procedure(Request $request)
    {
        $request->validate([
            'Item_Tractors' => 'required|string',
            'Name_Tractor' => 'required|string',
            'Name_Area' => 'required|string',
        ]);

        $Name_Tractor = $request->input('Name_Tractor');
        $Name_Area = $request->input('Name_Area');
        $lines = explode(PHP_EOL, $request->input('Item_Tractors'));

        $proceduresInDB = Procedure::where('Name_Tractor', $Name_Tractor)
            ->where('Name_Area', $Name_Area)
            ->pluck('Name_Procedure')
            ->toArray();

        $updated = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (! $line) {
                continue;
            }

            // Pisahkan berdasarkan tab (\t)
            $parts = explode("\t", $line);
            $nameProcedure = trim($parts[0] ?? '');
            $itemProcedure = trim($parts[1] ?? '');

            // Hanya proses jika nama prosedur valid dan ada di DB
            if ($nameProcedure && in_array($nameProcedure, $proceduresInDB)) {
                // Update di tabel procedures
                Procedure::where('Name_Tractor', $Name_Tractor)
                    ->where('Name_Area', $Name_Area)
                    ->where('Name_Procedure', $nameProcedure)
                    ->update(['Item_Procedure' => $itemProcedure]);

                // 🔁 Sinkronisasi ke List_Report — hanya jika belum dikirim
                $matchingListReports = \App\Models\List_Report::where([
                    'Name_Tractor' => $Name_Tractor,
                    'Name_Area' => $Name_Area,
                    'Name_Procedure' => $nameProcedure,
                ])->get();

                foreach ($matchingListReports as $listReport) {
                    // ✅ Cek: apakah laporan BELUM PERNAH dikirim?
                    $isNotSubmitted =
                        is_null($listReport->Time_List_Report) &&
                        is_null($listReport->Time_Approved_Leader) &&
                        is_null($listReport->Time_Approved_Auditor);

                    if ($isNotSubmitted) {
                        // Update Item_Procedure dengan nilai BARU
                        DB::table('list_reports')
                            ->where('Id_List_Report', $listReport->Id_List_Report)
                            ->update([
                                'Item_Procedure' => $itemProcedure, // ← nilai baru dari input
                                // Status tetap null (tidak perlu di-set ulang, tapi boleh untuk kejelasan)
                                'Time_List_Report' => null,
                                'Time_Approved_Leader' => null,
                                'Time_Approved_Auditor' => null,
                                'Leader_Name' => null,
                                'Auditor_Name' => null,
                            ]);
                    }
                    // ❌ Jika sudah dikirim → abaikan
                }

                $updated = true;
            }
        }

        if ($updated) {
            return back()->with('success', 'Matching procedures and related reports updated successfully');
        }

        return back();
    }

    public function index_missing()
    {
        $page = 'missing';

        // Hanya ambil tractor yang memiliki procedure tanpa PIC
        $tractors = Tractor::whereHas('procedures', function ($query) {
            $query->whereNull('Pic_Procedure')
                ->orWhereRaw('JSON_LENGTH(Pic_Procedure) = 0');
        })->orderBy('Name_Tractor', 'asc')->get();

        // Hitung jumlah procedure tanpa PIC untuk setiap tractor
        // well this code will be very slow on large datasets since this same as query runs N times where N is number of tractors
        //        $tractorProcedureCounts = [];
        //        foreach ($tractors as $tractor) {
        //            $tractorProcedureCounts[$tractor->Name_Tractor]['missing'] = Procedure::where('Name_Tractor', $tractor->Name_Tractor)
        //                ->where(function($query) {
        //                    $query->whereNull('Pic_Procedure')
        //                        ->orWhereRaw('JSON_LENGTH(Pic_Procedure) = 0');
        //                })
        //                ->count();
        //        }

        // Optimized query to get counts in one go
        $tractorProcedurelist = Procedure::all();

        $tractorProcedureCounts = [];
        foreach ($tractorProcedurelist as $tractorProcedure) {
            if (! isset($tractorProcedureCounts[$tractorProcedure->Name_Tractor])) {
                $tractorProcedureCounts[$tractorProcedure->Name_Tractor] = [
                    'missing' => 0,
                    'has' => 0,
                    'total' => 0,
                    'percent' => 0,
                ];
            }
            if (is_null($tractorProcedure->Pic_Procedure)) {
                $tractorProcedureCounts[$tractorProcedure->Name_Tractor]['missing'] += 1;
            }
            if (! is_null($tractorProcedure->Pic_Procedure)) {
                $tractorProcedureCounts[$tractorProcedure->Name_Tractor]['has'] += 1;
            }
            if (isset($tractorProcedureCounts[$tractorProcedure->Name_Tractor]['missing']) && isset($tractorProcedureCounts[$tractorProcedure->Name_Tractor]['has'])) {
                $tractorProcedureCounts[$tractorProcedure->Name_Tractor]['total'] = $tractorProcedureCounts[$tractorProcedure->Name_Tractor]['missing'] + $tractorProcedureCounts[$tractorProcedure->Name_Tractor]['has'];
                if ($tractorProcedureCounts[$tractorProcedure->Name_Tractor]['total'] > 0) {
                    $tractorProcedureCounts[$tractorProcedure->Name_Tractor]['percent'] = round(($tractorProcedureCounts[$tractorProcedure->Name_Tractor]['has'] / $tractorProcedureCounts[$tractorProcedure->Name_Tractor]['total']) * 100, 0);
                } else {
                    $tractorProcedureCounts[$tractorProcedure->Name_Tractor]['percent'] = 0;
                }
            }
        }

        debugbar()->info($tractorProcedureCounts);
        debugbar()->info($tractorProcedurelist);

        return view('leaders.missing.index', compact('page', 'tractors', 'tractorProcedureCounts'));
    }

    public function index_area_missing($Name_Tractor)
    {
        $page = 'missing';
        $tractor = $Name_Tractor;
        $photoTractor = Tractor::where('Name_Tractor', $Name_Tractor)->value('Photo_Tractor');

        // Hanya ambil area yang memiliki procedure tanpa PIC
        $areas = Area::where('Name_Tractor', $Name_Tractor)
            ->whereHas('procedures', function ($query) {
                $query->whereNull('Pic_Procedure')
                    ->orWhereRaw('JSON_LENGTH(Pic_Procedure) = 0');
            })
            ->orderBy('Name_Area', 'asc')
            ->get();

        return view('leaders.missing.areas', compact('page', 'tractor', 'photoTractor', 'areas'));
    }

    public function index_procedure_missing($Name_Tractor, $Name_Area)
    {
        $page = 'missing';
        $tractor = $Name_Tractor;
        $photoTractor = Tractor::where('Name_Tractor', $Name_Tractor)->value('Photo_Tractor');
        $area = $Name_Area;

        // Hanya ambil procedure yang tidak memiliki PIC
        $procedures = Procedure::where('Name_Tractor', $Name_Tractor)
            ->where('Name_Area', $Name_Area)
            ->where(function ($query) {
                $query->whereNull('Pic_Procedure')
                    ->orWhereRaw('JSON_LENGTH(Pic_Procedure) = 0');
            })
            ->orderBy('Name_Procedure', 'asc')
            ->get();

        // Ambil daftar training (Report) yang tersedia untuk assignment
        $trainings = \App\Models\Report::with('member')
            ->orderBy('Start_Report', 'desc')
            ->get();

        return view('leaders.missing.procedures', compact('page', 'tractor', 'photoTractor', 'area', 'procedures', 'trainings'));
    }

    public function assign_to_training(Request $request)
    {
        $request->validate([
            'Id_Procedure' => 'required|exists:procedures,Id_Procedure',
            'Id_Report' => 'required|array',
            'Id_Report.*' => 'exists:reports,Id_Report',
        ], [
            'Id_Report.required' => 'Pilih minimal satu training',
            'Id_Report.array' => 'Format training tidak valid',
        ]);

        $procedure = Procedure::findOrFail($request->Id_Procedure);
        $successCount = 0;
        $skipCount = 0;
        $memberIds = [];

        foreach ($request->Id_Report as $reportId) {
            $report = \App\Models\Report::with('member')->findOrFail($reportId);

            // Cek apakah procedure sudah ada di list report ini
            $exists = \App\Models\List_Report::where('Id_Report', $report->Id_Report)
                ->where('Name_Procedure', $procedure->Name_Procedure)
                ->where('Name_Area', $procedure->Name_Area)
                ->where('Name_Tractor', $procedure->Name_Tractor)
                ->exists();

            if ($exists) {
                $skipCount++;

                continue;
            }

            $name_member = $report->member->Name_Member ?? 'Unknown';
            $id_member = $report->Id_Member;
            $timeReport = \Carbon\Carbon::parse($report->Start_Report)->format('Y-m-d');
            $fullPath = 'reports/'.$timeReport.'_'.$id_member;

            // Tambahkan ke list_reports
            \App\Models\List_Report::create([
                'Id_Report' => $report->Id_Report,
                'Name_Procedure' => $procedure->Name_Procedure,
                'Name_Area' => $procedure->Name_Area,
                'Name_Tractor' => $procedure->Name_Tractor,
                'Item_Procedure' => $procedure->Item_Procedure,
                'Time_List_Report' => null,
                'Time_Approved_Leader' => null,
                'Time_Approved_Auditor' => null,
                'Reporter_Name' => $name_member,
                'Leader_Name' => null,
                'Auditor_Name' => null,
            ]);

            // Copy PDF ke folder report
            $sourcePath = 'procedures/'.$procedure->Name_Tractor.'/'.$procedure->Name_Area.'/'.$procedure->Name_Procedure.'.pdf';
            $targetPath = $fullPath.'/'.$procedure->Name_Procedure.'.pdf';

            if (Storage::disk('public')->exists($sourcePath)) {
                Storage::disk('public')->copy($sourcePath, $targetPath);
            }

            // Kumpulkan member IDs untuk update Pic_Procedure
            if (! in_array($id_member, $memberIds)) {
                $memberIds[] = $id_member;
            }

            $successCount++;
        }

        // Update Pic_Procedure dengan menambahkan member IDs baru
        if (! empty($memberIds)) {
            $currentPics = $procedure->Pic_Procedure ?? [];

            // Merge dengan PIC yang sudah ada (hindari duplikat)
            $updatedPics = array_unique(array_merge($currentPics, $memberIds));

            // Update procedure
            $procedure->Pic_Procedure = $updatedPics;
            $procedure->save();
        }

        $message = "Berhasil menambahkan procedure ke {$successCount} training";
        if ($skipCount > 0) {
            $message .= " ({$skipCount} training dilewati karena sudah ada)";
        }
        $message .= ' dan menambahkan '.count($memberIds).' PIC';

        return back()->with('success', $message);
    }
}
