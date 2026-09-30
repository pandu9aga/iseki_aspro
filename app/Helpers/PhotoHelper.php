<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoHelper
{
    /**
     * Get directory path for storing photos for a given report type and id.
     *
     * @param string $type 'report'|'training'|'replacement'
     * @param int|string $id
     * @param string $role 'member'|'leader'|'auditor'
     * @return string
     */
    public static function getDirectory(string $type, $id, string $role): string
    {
        $typePlural = $type === 'training' ? 'trainings' : ($type === 'replacement' ? 'replacements' : 'reports');
        return "{$typePlural}/photos/{$id}/{$role}";
    }

    /**
     * Add a photo file to storage and update model's Photos json.
     *
     * @param mixed $existingPhotos
     * @param string $type
     * @param int|string $id
     * @param string $role
     * @param \Illuminate\Http\UploadedFile $file
     * @return array
     */
    public static function addPhoto($existingPhotos, string $type, $id, string $role, $file): array
    {
        $current = is_array($existingPhotos) ? $existingPhotos : (json_decode($existingPhotos ?? '', true) ?? []);
        $rolePhotos = $current[$role] ?? [];

        $dir = self::getDirectory($type, $id, $role);
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }

        $filename = Str::random(16) . '_' . time() . '.' . $file->getClientOriginalExtension();
        $targetPath = $dir . '/' . $filename;

        Storage::disk('public')->put($targetPath, file_get_contents($file->getRealPath()));

        $rolePhotos[] = [
            'id' => 'photo_' . Str::random(10),
            'path' => $targetPath,
            'name' => $file->getClientOriginalName(),
            'uploaded_at' => now()->toDateTimeString(),
        ];

        $current[$role] = array_values($rolePhotos);
        return $current;
    }

    /**
     * Delete a photo by photo_id from disk and JSON.
     *
     * @param mixed $existingPhotos
     * @param string $role
     * @param string $photoId
     * @return array
     */
    public static function deletePhoto($existingPhotos, string $role, string $photoId): array
    {
        $current = is_array($existingPhotos) ? $existingPhotos : (json_decode($existingPhotos ?? '', true) ?? []);
        $rolePhotos = $current[$role] ?? [];

        $updatedList = [];
        foreach ($rolePhotos as $photo) {
            if (($photo['id'] ?? '') === $photoId) {
                if (!empty($photo['path']) && Storage::disk('public')->exists($photo['path'])) {
                    Storage::disk('public')->delete($photo['path']);
                }
            } else {
                $updatedList[] = $photo;
            }
        }

        $current[$role] = array_values($updatedList);
        return $current;
    }

    /**
     * Remove all photos for a role (e.g. during reset).
     */
    public static function removeRolePhotos($existingPhotos, string $type, $id, string $role): array
    {
        $current = is_array($existingPhotos) ? $existingPhotos : (json_decode($existingPhotos ?? '', true) ?? []);
        $order = ['member', 'leader', 'auditor'];
        $startIdx = array_search($role, $order);

        if ($startIdx === false) {
            return $current;
        }

        foreach (array_slice($order, $startIdx) as $r) {
            $dir = self::getDirectory($type, $id, $r);
            if (Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->deleteDirectory($dir);
            }
            unset($current[$r]);
        }

        return $current;
    }
}
