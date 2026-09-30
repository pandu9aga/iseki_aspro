<?php

namespace App\Http\Controllers;

use App\Helpers\QrHelper;
use App\Helpers\PhotoHelper;
use App\Models\List_Report;
use App\Models\List_Training;
use App\Models\ListReportReplacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemManagementController extends Controller
{
    /**
     * Resolve the target list model by type ('report', 'training', 'replacement') and id.
     */
    protected function getModel(string $type, $id)
    {
        return match ($type) {
            'training' => List_Training::findOrFail($id),
            'replacement' => ListReportReplacement::findOrFail($id),
            default => List_Report::findOrFail($id),
        };
    }

    /**
     * Get current user role from session ('member', 'leader', 'auditor').
     */
    protected function getCurrentSessionRole(): ?string
    {
        if (session()->has('Id_Member')) {
            return 'member';
        }
        $type = session('Id_Type_User');
        if ($type == 2) {
            return 'leader';
        }
        if ($type == 1) {
            return 'auditor';
        }
        return null;
    }

    /**
     * Add a QR code string.
     */
    public function addQr(Request $request, string $type, $id)
    {
        $request->validate([
            'role' => 'required|in:member,leader,auditor',
            'qr' => 'required|string',
        ]);

        $currentSessionRole = $this->getCurrentSessionRole();
        $targetRole = $request->input('role');
        if ($currentSessionRole && $currentSessionRole !== $targetRole) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya berhak mengelola QR Code untuk role ' . strtoupper($currentSessionRole),
            ], 403);
        }

        $item = $this->getModel($type, $id);
        $role = $targetRole;
        $rawQr = $request->input('qr');

        $updatedQrs = QrHelper::addQr($item->Qr_Codes, $role, $rawQr);
        $item->Qr_Codes = $updatedQrs;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'QR Code berhasil ditambahkan',
            'qr_codes' => $updatedQrs,
        ]);
    }

    /**
     * Update a QR code string at specific index.
     */
    public function updateQr(Request $request, string $type, $id)
    {
        $request->validate([
            'role' => 'required|in:member,leader,auditor',
            'index' => 'required|integer|min:0',
            'qr' => 'required|string',
        ]);

        $currentSessionRole = $this->getCurrentSessionRole();
        $targetRole = $request->input('role');
        if ($currentSessionRole && $currentSessionRole !== $targetRole) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya berhak mengelola QR Code untuk role ' . strtoupper($currentSessionRole),
            ], 403);
        }

        $item = $this->getModel($type, $id);
        $role = $targetRole;
        $index = (int) $request->input('index');
        $rawQr = $request->input('qr');

        $updatedQrs = QrHelper::updateQr($item->Qr_Codes, $role, $index, $rawQr);
        $item->Qr_Codes = $updatedQrs;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'QR Code berhasil diperbarui',
            'qr_codes' => $updatedQrs,
        ]);
    }

    /**
     * Delete a QR code at specific index.
     */
    public function deleteQr(Request $request, string $type, $id)
    {
        $request->validate([
            'role' => 'required|in:member,leader,auditor',
            'index' => 'required|integer|min:0',
        ]);

        $currentSessionRole = $this->getCurrentSessionRole();
        $targetRole = $request->input('role');
        if ($currentSessionRole && $currentSessionRole !== $targetRole) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya berhak mengelola QR Code untuk role ' . strtoupper($currentSessionRole),
            ], 403);
        }

        $item = $this->getModel($type, $id);
        $role = $targetRole;
        $index = (int) $request->input('index');

        $updatedQrs = QrHelper::deleteQr($item->Qr_Codes, $role, $index);
        $item->Qr_Codes = $updatedQrs;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'QR Code berhasil dihapus',
            'qr_codes' => $updatedQrs,
        ]);
    }

    /**
     * Upload photo for a role.
     */
    public function uploadPhoto(Request $request, string $type, $id)
    {
        $request->validate([
            'role' => 'required|in:member,leader,auditor',
            'photo' => 'required|image|max:20480',
        ]);

        $currentSessionRole = $this->getCurrentSessionRole();
        $targetRole = $request->input('role');
        if ($currentSessionRole && $currentSessionRole !== $targetRole) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya berhak mengunggah foto untuk role ' . strtoupper($currentSessionRole),
            ], 403);
        }

        $item = $this->getModel($type, $id);
        $role = $targetRole;

        $updatedPhotos = PhotoHelper::addPhoto(
            $item->Photos,
            $type,
            $id,
            $role,
            $request->file('photo')
        );

        $item->Photos = $updatedPhotos;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Foto berhasil diunggah',
            'photos' => $updatedPhotos,
        ]);
    }

    /**
     * Delete a photo.
     */
    public function deletePhoto(Request $request, string $type, $id)
    {
        $request->validate([
            'role' => 'required|in:member,leader,auditor',
            'photo_id' => 'required|string',
        ]);

        $currentSessionRole = $this->getCurrentSessionRole();
        $targetRole = $request->input('role');
        if ($currentSessionRole && $currentSessionRole !== $targetRole) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya berhak menghapus foto untuk role ' . strtoupper($currentSessionRole),
            ], 403);
        }

        $item = $this->getModel($type, $id);
        $role = $targetRole;
        $photoId = $request->input('photo_id');

        $updatedPhotos = PhotoHelper::deletePhoto(
            $item->Photos,
            $role,
            $photoId
        );

        $item->Photos = $updatedPhotos;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Foto berhasil dihapus',
            'photos' => $updatedPhotos,
        ]);
    }

    /**
     * Get master PDF url and annotations for the item.
     */
    public function getAnnotations(string $type, $id)
    {
        // Load relasi yang relevan sesuai tipe
        $item = match ($type) {
            'training' => List_Training::with('training')->findOrFail($id),
            'replacement' => ListReportReplacement::findOrFail($id),
            default => List_Report::with('report')->findOrFail($id),
        };

        // Path to clean master PDF
        $masterPath = 'procedures/' . $item->Name_Tractor . '/' . $item->Name_Area . '/' . $item->Name_Procedure . '.pdf';
        if (Storage::disk('public')->exists($masterPath)) {
            $masterUrl = asset('storage/' . $masterPath);
        } else {
            $masterUrl = asset('storage/procedures/' . $item->Name_Tractor . '/' . $item->Name_Area . '/' . $item->Name_Procedure . '.pdf');
        }

        // Resolve member name safely
        $memberName = $item->Reporter_Name ?? 'Member';
        if ($type === 'report' && isset($item->report)) {
            $memberName = $item->report->member->Name_Member ?? $memberName;
        } elseif ($type === 'training' && isset($item->training)) {
            $memberName = $item->training->member->Name_Member ?? $memberName;
        }

        return response()->json([
            'success' => true,
            'master_pdf_url' => $masterUrl,
            'name_procedure' => $item->Name_Procedure,
            'timestamps' => [
                'member' => $item->Time_List_Report,
                'leader' => $item->Time_Approved_Leader,
                'auditor' => $item->Time_Approved_Auditor,
            ],
            'names' => [
                'member' => $memberName,
                'leader' => $item->Leader_Name ?? 'Leader',
                'auditor' => $item->Auditor_Name ?? 'Auditor',
            ],
            'annotations' => $item->Annotations ?? [],
            'qr_codes' => $item->Qr_Codes ?? [],
            'photos' => $item->Photos ?? [],
        ]);
    }

    /**
     * Save annotations JSON for a role.
     */
    public function saveAnnotations(Request $request, string $type, $id)
    {
        $request->validate([
            'role' => 'required|in:member,leader,auditor',
            'annotations' => 'nullable',
        ]);

        $item = $this->getModel($type, $id);
        $role = $request->input('role');

        $updatedAnnotations = \App\Helpers\AnnotationHelper::saveRoleAnnotations(
            $item->Annotations,
            $role,
            $request->input('annotations')
        );

        $item->Annotations = $updatedAnnotations;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Coretan berhasil disimpan',
            'annotations' => $updatedAnnotations,
        ]);
    }
}
