<?php

namespace App\Helpers;

class QrHelper
{
    /**
     * Parse and sanitize array of QR code strings, taking only first 3 semicolon-separated parts.
     *
     * @param mixed $qrInput
     * @return array
     */
    public static function sanitizeQrList($qrInput): array
    {
        if (empty($qrInput)) {
            return [];
        }

        if (is_string($qrInput)) {
            $decoded = json_decode($qrInput, true);
            $qrList = is_array($decoded) ? $decoded : explode(',', $qrInput);
        } elseif (is_array($qrInput)) {
            $qrList = $qrInput;
        } else {
            $qrList = [];
        }

        $sanitized = [];
        foreach ($qrList as $item) {
            if (!is_string($item)) {
                continue;
            }
            $trimmed = trim($item);
            if ($trimmed === '') {
                continue;
            }

            // Split by semicolon and keep only first 3 parts
            $parts = explode(';', $trimmed);
            $slice = array_slice($parts, 0, 3);
            $cleanStr = implode(';', array_map('trim', $slice));

            if ($cleanStr !== '' && !in_array($cleanStr, $sanitized)) {
                $sanitized[] = $cleanStr;
            }
        }

        return $sanitized;
    }

    /**
     * Merge sanitized QR codes into model's existing Qr_Codes JSON for a specific user type (role).
     *
     * @param mixed $existingQrCodes
     * @param string $role 'member'|'leader'|'auditor'
     * @param mixed $newQrs
     * @return array
     */
    public static function mergeQrCodes($existingQrCodes, string $role, $newQrs): array
    {
        $current = [];
        if (is_string($existingQrCodes)) {
            $current = json_decode($existingQrCodes, true) ?? [];
        } elseif (is_array($existingQrCodes)) {
            $current = $existingQrCodes;
        }

        $existingRoleQrs = isset($current[$role]) && is_array($current[$role]) ? $current[$role] : [];
        $sanitized = self::sanitizeQrList($newQrs);

        // Gabungkan QR yang sudah ada sebelumnya dengan QR baru yang discan (skip jika duplikat)
        foreach ($sanitized as $qr) {
            if (!in_array($qr, $existingRoleQrs)) {
                $existingRoleQrs[] = $qr;
            }
        }

        $current[$role] = array_values($existingRoleQrs);

        return $current;
    }

    /**
     * Remove QR codes for a specific role and all roles that come after it in the approval chain.
     * Approval order: member -> leader -> auditor.
     *
     * @param mixed  $existingQrCodes
     * @param string $role 'member'|'leader'|'auditor'
     * @return array
     */
    public static function removeRole($existingQrCodes, string $role): array
    {
        $current = [];
        if (is_string($existingQrCodes)) {
            $current = json_decode($existingQrCodes, true) ?? [];
        } elseif (is_array($existingQrCodes)) {
            $current = $existingQrCodes;
        }

        $order = ['member', 'leader', 'auditor'];
        $startIdx = array_search($role, $order);

        if ($startIdx === false) {
            return $current;
        }

        foreach (array_slice($order, $startIdx) as $r) {
            unset($current[$r]);
        }

        return $current;
    }

    /**
     * Add a single QR to a role's array.
     */
     public static function addQr($existingQrCodes, string $role, string $rawQr): array
     {
         if (is_string($existingQrCodes)) {
             $current = json_decode($existingQrCodes, true) ?? [];
         } elseif (is_array($existingQrCodes)) {
             $current = $existingQrCodes;
         } else {
             $current = [];
         }
         $roleList = isset($current[$role]) && is_array($current[$role]) ? array_values($current[$role]) : [];

         $parts = explode(';', trim($rawQr));
         $slice = array_slice($parts, 0, 3);
         $cleanStr = implode(';', array_map('trim', $slice));

         if ($cleanStr !== '' && !in_array($cleanStr, $roleList)) {
             $roleList[] = $cleanStr;
         }
         $current[$role] = array_values($roleList);
         return $current;
     }

     /**
      * Update a QR code at a specific index for a role.
      */
     public static function updateQr($existingQrCodes, string $role, int $index, string $rawQr): array
     {
         if (is_string($existingQrCodes)) {
             $current = json_decode($existingQrCodes, true) ?? [];
         } elseif (is_array($existingQrCodes)) {
             $current = $existingQrCodes;
         } else {
             $current = [];
         }
         $roleList = isset($current[$role]) && is_array($current[$role]) ? array_values($current[$role]) : [];

         $parts = explode(';', trim($rawQr));
         $slice = array_slice($parts, 0, 3);
         $cleanStr = implode(';', array_map('trim', $slice));

         if ($cleanStr !== '') {
             if (isset($roleList[$index])) {
                 $roleList[$index] = $cleanStr;
             } else {
                 $roleList[] = $cleanStr;
             }
         }
         $current[$role] = array_values($roleList);
         return $current;
     }

     /**
      * Delete a QR code at a specific index for a role.
      */
     public static function deleteQr($existingQrCodes, string $role, int $index): array
     {
         if (is_string($existingQrCodes)) {
             $current = json_decode($existingQrCodes, true) ?? [];
         } elseif (is_array($existingQrCodes)) {
             $current = $existingQrCodes;
         } else {
             $current = [];
         }
         $roleList = isset($current[$role]) && is_array($current[$role]) ? array_values($current[$role]) : [];

         if (isset($roleList[$index])) {
             array_splice($roleList, $index, 1);
         }
         $current[$role] = array_values($roleList);
         return $current;
     }
}
