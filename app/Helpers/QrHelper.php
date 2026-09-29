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

        $sanitized = self::sanitizeQrList($newQrs);
        $current[$role] = $sanitized;

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
}
