<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Tractor;
use App\Models\Area;
use App\Models\Procedure;
use App\Models\User;

class ProcedureController extends Controller
{
    public function index()
    {
        $page = "procedure";
        $tractors = Tractor::orderBy('Name_Tractor', 'asc')->get();
        return view('leaders.procedures.index', compact('page', 'tractors'));
    }

    public function create_tractor(Request $request)
    {
        $request->validate([
            'Name_Tractor' => 'required|unique:tractors,Name_Tractor',
            'Photo_Tractor' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
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
            $filename = uniqid('tractor_') . '.' . $file->getClientOriginalExtension();
            $photoPath = 'storage/tractors/' . $filename;
            $file->move(public_path('storage/tractors'), $filename);
        }

        DB::table('tractors')->insert([
            'Name_Tractor' => $name,
            'Photo_Tractor' => $photoPath
        ]);

        Storage::disk('public')->makeDirectory('procedures/' . $name);

        return redirect()->route('procedure')->with('success', 'Tractor berhasil ditambahkan dan foto disimpan');
    }

    public function update_tractor(Request $request, string $Id_Tractor)
    {
        $oldTractor = DB::table('tractors')->where('Id_Tractor', $Id_Tractor)->first();
        $oldName = $oldTractor->Name_Tractor;
        $oldPhoto = $oldTractor->Photo_Tractor;

        $request->validate([
            'Name_Tractor' => 'required|unique:tractors,Name_Tractor,' . $Id_Tractor . ',Id_Tractor'
        ], [
            'Name_Tractor.required' => 'Nama wajib diisi'
        ]);

        $newName = $request->input('Name_Tractor');
        $photoPath = $oldPhoto;

        if ($request->hasFile('Photo_Tractor')) {
            $file = $request->file('Photo_Tractor');

            if ($oldPhoto && $oldPhoto !== 'storage/tractors/default.png' && Storage::disk('public')->exists(str_replace('storage/', '', $oldPhoto))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $oldPhoto));
            }

            $fileName = uniqid('tractor_') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/tractors'), $fileName);
            $photoPath = 'storage/tractors/' . $fileName;
        }

        DB::table('tractors')->where('Id_Tractor', $Id_Tractor)->update([
            'Name_Tractor' => $newName,
            'Photo_Tractor' => $photoPath
        ]);

        if ($oldName !== $newName) {
            $oldFolder = 'procedures/' . $oldName;
            $newFolder = 'procedures/' . $newName;

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
        $folderName = 'procedures/' . $nameTractor;

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
        $page = "procedure";
        $tractor = $Name_Tractor;
        $photoTractor = Tractor::where('Name_Tractor', $Name_Tractor)->value('Photo_Tractor');
        $areas = Area::where('Name_Tractor', $Name_Tractor)->orderBy('Name_Area', 'asc')->get();
        return view('leaders.procedures.areas', compact('page', 'tractor', 'photoTractor', 'areas'));
    }

    public function create_area(Request $request)
    {
        $request->validate([
            'Name_Tractor' => 'required',
            'Name_Area' => 'required'
        ], [
            'Name_Tractor.required' => 'Nama tractor wajib diisi',
            'Name_Area.required' => 'Nama area wajib diisi'
        ]);

        $Name_Tractor = $request->input('Name_Tractor');
        $Name_Area = $request->input('Name_Area');

        if (DB::table('areas')->where('Name_Tractor', $Name_Tractor)->where('Name_Area', $Name_Area)->exists()) {
            return back()->withErrors(['Nama area di tractor ini sudah ada'])->withInput();
        }

        DB::table('areas')->insert([
            'Name_Tractor' => $Name_Tractor,
            'Name_Area' => $Name_Area
        ]);

        Storage::disk('public')->makeDirectory("procedures/$Name_Tractor/$Name_Area");

        return redirect()
            ->route('procedure.area.index', ['Name_Tractor' => $Name_Tractor])
            ->with('success', 'Area berhasil ditambahkan dan folder dibuat');
    }

    public function update_area(Request $request, string $Id_Area)
    {
        $oldArea = DB::table('areas')->where('Id_Area', $Id_Area)->first();
        if (!$oldArea) {
            return back()->withErrors(['Area tidak ditemukan']);
        }

        $oldNameTractor = $oldArea->Name_Tractor;
        $oldNameArea = $oldArea->Name_Area;

        $request->validate([
            'Name_Tractor' => 'required',
            'Name_Area' => 'required'
        ], [
            'Name_Tractor.required' => 'Nama tractor wajib diisi',
            'Name_Area.required' => 'Nama area wajib diisi'
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
            'Name_Area' => $newNameArea
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
        $folderName = 'procedures/' . $Name_Tractor . '/' . $Name_Area;

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
        $page = "procedure";
        $tractor = $Name_Tractor;
        $photoTractor = Tractor::where('Name_Tractor', $Name_Tractor)->value('Photo_Tractor');
        $area = $Name_Area;
        $procedures = Procedure::where('Name_Tractor', $Name_Tractor)
            ->where('Name_Area', $Name_Area)
            ->orderBy('Name_Procedure', 'asc')
            ->get();
        return view('leaders.procedures.procedures', compact('page', 'tractor', 'photoTractor', 'area', 'procedures'));
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
                $path = 'procedures/' . $tractor . '/' . $area;

                $file->storeAs($path, $filename, 'public');

                DB::table('procedures')->updateOrInsert(
                    [
                        'Name_Tractor' => $tractor,
                        'Name_Area' => $area,
                        'Name_Procedure' => $nameProcedure
                    ],
                    [
                        'Item_Procedure' => ''
                    ]
                );

                // 🔥 Sinkronisasi List_Report
                $matchingListReports = \App\Models\List_Report::where([
                    'Name_Tractor' => $tractor,
                    'Name_Area' => $area,
                    'Name_Procedure' => $nameProcedure
                ])->get();

                foreach ($matchingListReports as $listReport) {
                    DB::table('list_reports')->where('Id_List_Report', $listReport->Id_List_Report)
                        ->update([
                            'Item_Procedure' => '',
                            'Time_List_Report' => null,
                            'Time_Approved_Leader' => null,
                            'Time_Approved_Auditor' => null,
                            'Leader_Name' => null,
                            'Auditor_Name' => null,
                        ]);

                    // Copy PDF ke reports
                    $reportFolder = $listReport->report;
                    if ($reportFolder) {
                        $startDate = \Carbon\Carbon::parse($reportFolder->Start_Report)->format('Y-m-d');
                        $targetFolder = "reports/{$startDate}_{$reportFolder->Id_Member}";
                        Storage::disk('public')->makeDirectory($targetFolder);

                        Storage::disk('public')->copy(
                            "procedures/{$tractor}/{$area}/{$listReport->Name_Procedure}.pdf",
                            "{$targetFolder}/{$listReport->Name_Procedure}.pdf"
                        );
                    }
                }
            }
        }

        return redirect()->route('procedure.procedure.index', [
            'Name_Tractor' => $tractor,
            'Name_Area' => $area
        ])->with('success', 'Prosedur berhasil ditambahkan atau diperbarui');
    }

    public function update_procedure(Request $request, string $Id_Procedure)
    {
        $oldProcedure = DB::table('procedures')->where('Id_Procedure', $Id_Procedure)->first();
        if (!$oldProcedure) {
            return back()->withErrors(['Procedure tidak ditemukan']);
        }

        $oldNameTractor = $oldProcedure->Name_Tractor;
        $oldNameArea = $oldProcedure->Name_Area;
        $oldNameProcedure = $oldProcedure->Name_Procedure;

        $request->validate([
            'Name_Tractor' => 'required',
            'Name_Area' => 'required',
            'Name_Procedure' => 'required'
        ], [
            'Name_Tractor.required' => 'Nama tractor wajib diisi',
            'Name_Area.required' => 'Nama area wajib diisi',
            'Name_Procedure.required' => 'Nama procedure wajib diisi'
        ]);

        $newNameTractor = $request->input('Name_Tractor');
        $newNameArea = $request->input('Name_Area');
        $newNameProcedure = $request->input('Name_Procedure');
        $newItemProcedure = $request->input('Item_Procedure') ?? '';

        if (DB::table('procedures')
            ->where('Name_Tractor', $newNameTractor)
            ->where('Name_Area', $newNameArea)
            ->where('Name_Procedure', $newNameProcedure)
            ->where('Id_Procedure', '!=', $Id_Procedure)
            ->exists()
        ) {
            return back()->withErrors(['Nama procedure di area tractor ini sudah ada'])->withInput();
        }

        DB::table('procedures')->where('Id_Procedure', $Id_Procedure)->update([
            'Name_Tractor' => $newNameTractor,
            'Name_Area' => $newNameArea,
            'Name_Procedure' => $newNameProcedure,
            'Item_Procedure' => $newItemProcedure
        ]);

        // Jika ada perubahan nama, rename file
        if (
            $oldNameTractor !== $newNameTractor ||
            $oldNameArea !== $newNameArea ||
            $oldNameProcedure !== $newNameProcedure
        ) {
            $oldPath = 'procedures/' . $oldNameTractor . '/' . $oldNameArea . '/' . $oldNameProcedure . '.pdf';
            $newDir = 'procedures/' . $newNameTractor . '/' . $newNameArea;
            $newFileName = $newNameProcedure . '.pdf';
            $newPath = $newDir . '/' . $newFileName;

            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->makeDirectory($newDir);
                Storage::disk('public')->move($oldPath, $newPath);
            }
        }

        // 🔥 Sinkronisasi List_Report bila nama berubah
        $matchingListReports = \App\Models\List_Report::where([
            'Name_Tractor' => $oldNameTractor,
            'Name_Area' => $oldNameArea,
            'Name_Procedure' => $oldNameProcedure
        ])->get();

        foreach ($matchingListReports as $listReport) {

            // Update field List_Report
            DB::table('list_reports')->where('Id_List_Report', $listReport->Id_List_Report)
                ->update([
                    'Name_Tractor' => $newNameTractor,
                    'Name_Area' => $newNameArea,
                    'Name_Procedure' => $newNameProcedure,
                    'Item_Procedure' => $newItemProcedure,

                    // reset status
                    'Time_List_Report' => null,
                    'Time_Approved_Leader' => null,
                    'Time_Approved_Auditor' => null,
                    'Leader_Name' => null,
                    'Auditor_Name' => null,
                ]);

            // Replace PDF di folder reports
            $report = $listReport->report;
            if ($report) {
                $startDate = \Carbon\Carbon::parse($report->Start_Report)->format('Y-m-d');
                $targetDir = "reports/{$startDate}_{$report->Id_Member}";
                $sourcePdfPath = "procedures/{$newNameTractor}/{$newNameArea}/{$newNameProcedure}.pdf";

                Storage::disk('public')->makeDirectory($targetDir);
                Storage::disk('public')->copy(
                    $sourcePdfPath,
                    "{$targetDir}/{$newNameProcedure}.pdf"
                );
            }
        }

        return redirect()->route('procedure.procedure.index', ['Name_Tractor' => $newNameTractor, 'Name_Area' => $newNameArea])
            ->with('success', 'Data dan file berhasil diedit');
    }

    public function upload_procedure(Request $request, string $Id_Procedure)
    {
        $request->validate([
            'File_Procedure' => 'required|mimes:pdf',
        ]);

        $procedure = DB::table('procedures')->where('Id_Procedure', $Id_Procedure)->first();
        if (!$procedure) {
            return back()->withErrors(['Procedure tidak ditemukan']);
        }

        $nameTractor = $procedure->Name_Tractor;
        $nameArea = $procedure->Name_Area;
        $nameProcedure = $procedure->Name_Procedure;

        $folderPath = 'procedures/' . $nameTractor . '/' . $nameArea;
        $fileName = $nameProcedure . '.pdf';

        $file = $request->file('File_Procedure');
        Storage::disk('public')->putFileAs($folderPath, $file, $fileName);

        // 🔥 PERBAIKAN: Sinkronisasi otomatis ke semua laporan
        $matchingListReports = \App\Models\List_Report::where('Name_Procedure', $nameProcedure)
            ->where('Name_Area', $nameArea)
            ->where('Name_Tractor', $nameTractor)
            ->get();

        foreach ($matchingListReports as $listReport) {
            DB::table('list_reports')->where('Id_List_Report', $listReport->Id_List_Report)
                ->update([
                    'Item_Procedure' => $procedure->Item_Procedure ?? '',
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
                $sourcePdfPath = "procedures/{$nameTractor}/{$nameArea}/{$nameProcedure}.pdf";

                Storage::disk('public')->makeDirectory($targetDir);
                Storage::disk('public')->copy(
                    $sourcePdfPath,
                    "{$targetDir}/{$nameProcedure}.pdf"
                );
            }
        }

        return redirect()
            ->route('procedure.procedure.index', [
                'Name_Tractor' => $nameTractor,
                'Name_Area' => $nameArea
            ])
            ->with('success', 'File procedure berhasil diperbarui');
    }

    public function destroy_procedure($Id_Procedure)
    {
        $procedure = Procedure::findOrFail($Id_Procedure);
        $Name_Tractor = $procedure->Name_Tractor;
        $Name_Area = $procedure->Name_Area;
        $Name_Procedure = $procedure->Name_Procedure;
        $filePath = 'procedures/' . $Name_Tractor . '/' . $Name_Area . '/' . $Name_Procedure . '.pdf';

        $procedure->delete();

        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        return redirect()
            ->route('procedure.procedure.index', [
                'Name_Tractor' => $Name_Tractor,
                'Name_Area' => $Name_Area
            ])
            ->with('success', 'File procedure berhasil dihapus');
    }

    public function insert_item_procedure(Request $request)
    {
        $request->validate([
            'Item_Tractors' => 'required|string',
            'Name_Tractor' => 'required|string',
            'Name_Area' => 'required|string'
        ]);

        $Name_Tractor = $request->input('Name_Tractor');
        $Name_Area = $request->input('Name_Area');
        $lines = explode(PHP_EOL, $request->input('Item_Tractors'));

        $proceduresInDB = Procedure::where('Name_Tractor', $Name_Tractor)
            ->where('Name_Area', $Name_Area)
            ->pluck('Name_Procedure')
            ->toArray();

        $inserted = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) continue;

            $parts = explode("\t", $line);
            $nameProcedure = trim($parts[0] ?? '');
            $itemProcedure = trim($parts[1] ?? '');

            if ($nameProcedure && in_array($nameProcedure, $proceduresInDB)) {
                Procedure::where('Name_Tractor', $Name_Tractor)
                    ->where('Name_Area', $Name_Area)
                    ->where('Name_Procedure', $nameProcedure)
                    ->update(['Item_Procedure' => $itemProcedure]);
                $inserted = true;
            }
        }

        if ($inserted) {
            return back()->with('success', 'Matching procedures updated successfully');
        }

        return back();
    }
}
