<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Tractor;
use App\Models\Area;
use App\Models\Procedure;

class ProcedureController extends Controller
{
    public function index(){
        $page = "procedure";

        $tractors = Tractor::orderBy('Name_Tractor', 'asc')->get();
        return view('leaders.procedures.index', compact('page', 'tractors'));
    }
    
    public function create_tractor(Request $request)
    {
        // Validasi
        $request->validate([
            'Name_Tractor' => 'required|unique:tractors,Name_Tractor'
        ], [
            'Name_Tractor.required' => 'Nama wajib diisi'
        ]);

        $name = $request->input('Name_Tractor');

        // Simpan ke database
        DB::table('tractors')->insert([
            'Name_Tractor' => $name
        ]);

        // Buat folder: storage/app/public/procedures/{Name_Tractor}
        Storage::disk('public')->makeDirectory('procedures/' . $name);

        return redirect()->route('procedure')->with('success', 'Tractor berhasil ditambahkan dan folder dibuat');
    }

    public function update_tractor(Request $request, string $Id_Tractor)
    {
        // Ambil data tractor sebelum diubah
        $oldTractor = DB::table('tractors')->where('Id_Tractor', $Id_Tractor)->first();
        $oldName = $oldTractor->Name_Tractor;

        // Validasi
        $request->validate([
            'Name_Tractor' => 'required|unique:tractors,Name_Tractor,' . $Id_Tractor . ',Id_Tractor'
        ], [
            'Name_Tractor.required' => 'Nama wajib diisi'
        ]);

        $newName = $request->input('Name_Tractor');

        // Update di database
        DB::table('tractors')->where('Id_Tractor', $Id_Tractor)->update([
            'Name_Tractor' => $newName
        ]);

        // Ubah nama folder jika nama berubah
        if ($oldName !== $newName) {
            $oldFolder = 'procedures/' . $oldName;
            $newFolder = 'procedures/' . $newName;

            if (Storage::disk('public')->exists($oldFolder)) {
                Storage::disk('public')->move($oldFolder, $newFolder);
            }

            // Ubah nama area dan procedure yang ada di tractor yang sama
            DB::table('areas')->where('Name_Tractor', $oldName)->update([
                'Name_Tractor' => $newName
            ]);
            DB::table('procedures')->where('Name_Tractor', $oldName)->update([
                'Name_Tractor' => $newName
            ]);
        }

        return redirect()->route('procedure')->with('success','Data dan folder berhasil diedit');
    }

    public function destroy_tractor($Id_Tractor)
    {
        // Ambil data tractor
        $tractor = Tractor::findOrFail($Id_Tractor);
        $nameTractor = $tractor->Name_Tractor;
        $folderName = 'procedures/' . $nameTractor;

        // Hapus semua area dan procedure yang terkait dengan tractor ini
        DB::table('areas')->where('Name_Tractor', $nameTractor)->delete();
        DB::table('procedures')->where('Name_Tractor', $nameTractor)->delete();

        // Hapus data tractor
        $tractor->delete();

        // Hapus folder jika ada
        if (Storage::disk('public')->exists($folderName)) {
            Storage::disk('public')->deleteDirectory($folderName);
        }

        return redirect()->route('procedure')->with('success','Data dan folder berhasil dihapus');
    }

    public function index_area($Name_Tractor){
        $page = "procedure";

        $tractor = $Name_Tractor;
        $areas = Area::where('Name_Tractor', $Name_Tractor)->orderBy('Name_Area', 'asc')->get();
        return view('leaders.procedures.areas', compact('page', 'tractor', 'areas'));
    }

    public function create_area(Request $request)
    {
        // Validasi basic (required saja dulu)
        $request->validate([
            'Name_Tractor' => 'required',
            'Name_Area' => 'required'
        ], [
            'Name_Tractor.required' => 'Nama tractor wajib diisi',
            'Name_Area.required' => 'Nama area wajib diisi'
        ]);

        $Name_Tractor = $request->input('Name_Tractor');
        $Name_Area = $request->input('Name_Area');

        // Cek apakah kombinasi sudah ada
        $exists = DB::table('areas')
            ->where('Name_Tractor', $Name_Tractor)
            ->where('Name_Area', $Name_Area)
            ->exists();

        if ($exists) {
            return back()->withErrors(['Nama area di tractor ini sudah ada'])->withInput();
        }

        // Simpan ke database
        DB::table('areas')->insert([
            'Name_Tractor' => $Name_Tractor,
            'Name_Area' => $Name_Area
        ]);

        // Buat folder: storage/app/public/procedures/{Name_Tractor}/{Name_Area}
        Storage::disk('public')->makeDirectory("procedures/$Name_Tractor/$Name_Area");

        return redirect()
            ->route('procedure.area.index', ['Name_Tractor' => $Name_Tractor])
            ->with('success', 'Area berhasil ditambahkan dan folder dibuat');
    }

    public function update_area(Request $request, string $Id_Area)
    {
        // Ambil data area sebelum diubah
        $oldArea = DB::table('areas')->where('Id_Area', $Id_Area)->first();
        if (!$oldArea) {
            return back()->withErrors(['Area tidak ditemukan']);
        }

        $oldNameTractor = $oldArea->Name_Tractor;
        $oldNameArea = $oldArea->Name_Area;

        // Validasi dasar
        $request->validate([
            'Name_Tractor' => 'required',
            'Name_Area' => 'required'
        ], [
            'Name_Tractor.required' => 'Nama tractor wajib diisi',
            'Name_Area.required' => 'Nama area wajib diisi'
        ]);

        $newNameTractor = $request->input('Name_Tractor');
        $newNameArea = $request->input('Name_Area');

        // Cek apakah kombinasi tractor+area yang baru sudah ada, selain yang sedang diedit
        $exists = DB::table('areas')
            ->where('Name_Tractor', $newNameTractor)
            ->where('Name_Area', $newNameArea)
            ->where('Id_Area', '!=', $Id_Area)
            ->exists();

        if ($exists) {
            return back()->withErrors(['Nama area di tractor ini sudah ada'])->withInput();
        }

        // Update database
        DB::table('areas')->where('Id_Area', $Id_Area)->update([
            'Name_Tractor' => $newNameTractor,
            'Name_Area' => $newNameArea
        ]);

        // Rename folder jika nama area atau nama tractornya berubah
        if ($oldNameTractor !== $newNameTractor || $oldNameArea !== $newNameArea) {
            $oldFolder = "procedures/$oldNameTractor/$oldNameArea";
            $newFolder = "procedures/$newNameTractor/$newNameArea";

            if (Storage::disk('public')->exists($oldFolder)) {
                Storage::disk('public')->makeDirectory("procedures/$newNameTractor");
                Storage::disk('public')->move($oldFolder, $newFolder);

                // Optional: hapus folder parent lama jika kosong
                $oldParent = "procedures/$oldNameTractor";
                if (empty(Storage::disk('public')->allFiles($oldParent)) &&
                    empty(Storage::disk('public')->allDirectories($oldParent))) {
                    Storage::disk('public')->deleteDirectory($oldParent);
                }

                // Ubah nama procedure yang ada di area dan tractor yang sama
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
        // Ambil data area
        $area = Area::findOrFail($Id_Area);
        $Name_Tractor = $area->Name_Tractor;
        $Name_Area = $area->Name_Area;
        $folderName = 'procedures/' . $Name_Tractor . '/' . $Name_Area;

        // Hapus semua procedure yang terkait dengan area dan tractor ini
        DB::table('procedures')
            ->where('Name_Tractor', $Name_Tractor)
            ->where('Name_Area', $Name_Area)
            ->delete();

        // Hapus data dari database
        $area->delete();

        // Hapus folder jika ada
        if (Storage::disk('public')->exists($folderName)) {
            Storage::disk('public')->deleteDirectory($folderName);
        }

        return redirect()->route('procedure.area.index', ['Name_Tractor' => $Name_Tractor])
            ->with('success','Data dan folder berhasil dihapus');
    }

    public function index_procedure($Name_Tractor, $Name_Area){
        $page = "procedure";

        $tractor = $Name_Tractor;
        $area = $Name_Area;
        $procedures = Procedure::where('Name_Tractor', $Name_Tractor)
            ->where('Name_Area', $Name_Area)
            ->orderBy('Name_Procedure', 'asc')
            ->get();
        return view('leaders.procedures.procedures', compact('page', 'tractor', 'area', 'procedures'));
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
                $nameProcedure = pathinfo($originalName, PATHINFO_FILENAME); // nama file tanpa ekstensi
                $filename = $originalName; // tidak pakai time(), agar bisa replace file dengan nama sama
                $path = 'procedures/' . $tractor . '/' . $area;

                // Simpan atau replace file PDF
                $file->storeAs($path, $filename, 'public');

                // Simpan atau update data di database
                DB::table('procedures')->updateOrInsert(
                    [
                        'Name_Tractor' => $tractor,
                        'Name_Area' => $area,
                        'Name_Procedure' => $nameProcedure,
                        'Item_Procedure' => ''
                    ]
                );
            }
        }

        return redirect()->route('procedure.procedure.index', [
            'Name_Tractor' => $tractor,
            'Name_Area' => $area
        ])->with('success', 'Prosedur berhasil ditambahkan atau diperbarui');
    }

    public function update_procedure(Request $request, string $Id_Procedure)
    {
        // Ambil data procedure sebelum diubah
        $oldProcedure = DB::table('procedures')->where('Id_Procedure', $Id_Procedure)->first();
        if (!$oldProcedure) {
            return back()->withErrors(['Procedure tidak ditemukan']);
        }

        $oldNameTractor = $oldProcedure->Name_Tractor;
        $oldNameArea = $oldProcedure->Name_Area;
        $oldNameProcedure = $oldProcedure->Name_Procedure;

        // Validasi dasar
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
        $newItemProcedure = '';

        if ($request->input('Item_Procedure') != '') {
            $newItemProcedure = $request->input('Item_Procedure');
        }

        // Cek apakah kombinasi tractor+area+procedure yang baru sudah ada, selain yang sedang diedit
        $exists = DB::table('procedures')
            ->where('Name_Tractor', $newNameTractor)
            ->where('Name_Area', $newNameArea)
            ->where('Name_Procedure', $newNameProcedure)
            ->where('Id_Procedure', '!=', $Id_Procedure)
            ->exists();

        if ($exists) {
            return back()->withErrors(['Nama procedure di area tractor ini sudah ada'])->withInput();
        }

        // Update database
        DB::table('procedures')->where('Id_Procedure', $Id_Procedure)->update([
            'Name_Tractor' => $newNameTractor,
            'Name_Area' => $newNameArea,
            'Name_Procedure' => $newNameProcedure,
            'Item_Procedure' => $newItemProcedure
        ]);

        // Rename file
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
                // Pastikan folder tujuan ada
                Storage::disk('public')->makeDirectory($newDir);

                // Rename file (pindah + rename)
                Storage::disk('public')->move($oldPath, $newPath);
            } else {
                // Catatan: jika file lama tidak ada, bisa ditangani di sini (opsional)
            }
        }

        return redirect()->route('procedure.procedure.index', ['Name_Tractor' => $newNameTractor, 'Name_Area' => $newNameArea])
            ->with('success', 'Data dan file berhasil diedit');
    }

    public function upload_procedure(Request $request, string $Id_Procedure)
    {
        // Validasi file wajib PDF
        $request->validate([
            'File_Procedure' => 'required|mimes:pdf',
        ]);

        // Ambil data procedure
        $procedure = DB::table('procedures')->where('Id_Procedure', $Id_Procedure)->first();
        if (!$procedure) {
            return back()->withErrors(['Procedure tidak ditemukan']);
        }

        $nameTractor = $procedure->Name_Tractor;
        $nameArea = $procedure->Name_Area;
        $nameProcedure = $procedure->Name_Procedure;

        // Tentukan path tujuan
        $folderPath = 'procedures/' . $nameTractor . '/' . $nameArea;
        $fileName = $nameProcedure . '.pdf';

        // Simpan file baru, timpa jika sudah ada
        $file = $request->file('File_Procedure');
        Storage::disk('public')->putFileAs($folderPath, $file, $fileName);

        return redirect()
            ->route('procedure.procedure.index', [
                'Name_Tractor' => $nameTractor,
                'Name_Area' => $nameArea
            ])
            ->with('success', 'File procedure berhasil diperbarui');
    }

    public function destroy_procedure($Id_Procedure)
    {
        // Ambil data procedure
        $procedure = Procedure::findOrFail($Id_Procedure);
        $Name_Tractor = $procedure->Name_Tractor;
        $Name_Area = $procedure->Name_Area;
        $Name_Procedure = $procedure->Name_Procedure;
        $filePath = 'procedures/' . $Name_Tractor . '/' . $Name_Area . '/' . $Name_Procedure . '.pdf';

        // Hapus data dari database
        $procedure->delete();

        // Hapus file PDF jika ada
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        return redirect()
            ->route('procedure.procedure.index', [
                'Name_Tractor' => $Name_Tractor,
                'Name_Area' => $Name_Area
            ])
            ->with('success', 'File procedure berhasil diperbarui');
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

        // Ambil semua prosedur yang ada untuk tractor + area ini
        $proceduresInDB = Procedure::where('Name_Tractor', $Name_Tractor)
            ->where('Name_Area', $Name_Area)
            ->pluck('Name_Procedure')
            ->toArray();

        $inserted = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) continue;

            // Ambil kode prosedur + deskripsi
            $parts = explode("\t", $line);
            $nameProcedure = trim($parts[0] ?? '');
            $itemProcedure = trim($parts[1] ?? '');

            if ($nameProcedure && in_array($nameProcedure, $proceduresInDB)) {
                Procedure::where('Name_Tractor', $Name_Tractor)
                    ->where('Name_Area', $Name_Area)
                    ->where('Name_Procedure', $nameProcedure)
                    ->update([
                        'Item_Procedure' => $itemProcedure
                    ]);
                $inserted = true;
            }
        }

        if ($inserted) {
            return back()->with('success', 'Matching procedures updated successfully');
        }

        return back(); // Tidak ada yang cocok, reload tanpa pesan
    }
}
