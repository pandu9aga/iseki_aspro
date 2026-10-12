<?php

namespace App\Helpers;

use Carbon\Carbon;

class AnnotationHelper
{
    /**
     * Save/replace annotations for a specific role and add a snapshot to history.
     *
     * @param mixed $existingAnnotations
     * @param string $role 'member'|'leader'|'auditor'
     * @param mixed $newAnnotations array or json string of annotation objects
     * @param string|null $username optional name of the user who submitted this
     * @return array
     */
    public static function saveRoleAnnotations($existingAnnotations, string $role, $newAnnotations, ?string $username = null): array
    {
        $parsedExisting = is_array($existingAnnotations) ? $existingAnnotations : (json_decode($existingAnnotations ?? '', true) ?? []);
        
        // Migrate old format to new format if needed
        if (!isset($parsedExisting['current']) && !isset($parsedExisting['history'])) {
            $current = [
                'current' => $parsedExisting,
                'history' => []
            ];
        } else {
            $current = $parsedExisting;
        }

        if (!isset($current['current'])) {
            $current['current'] = [];
        }
        if (!isset($current['history'])) {
            $current['history'] = [];
        }

        if (is_string($newAnnotations)) {
            $parsed = json_decode($newAnnotations, true);
            $items = is_array($parsed) ? $parsed : [];
        } elseif (is_array($newAnnotations)) {
            $items = $newAnnotations;
        } else {
            $items = [];
        }

        // Update the current state for this role
        $current['current'][$role] = array_values($items);

        // Append a snapshot to history
        $version = count($current['history']) + 1;
        $current['history'][] = [
            'version' => $version,
            'timestamp' => Carbon::now()->toDateTimeString(),
            'role' => $role,
            'name' => $username ?? session('Username_User', 'Unknown'),
            'annotations' => $current['current'] // Take snapshot of the full current state
        ];

        return $current;
    }

    /**
     * Remove annotations for a specific role (and subsequent roles in approval order).
     *
     * @param mixed $existingAnnotations
     * @param string $role
     * @param string|null $username optional name of the user who performed this action
     * @return array
     */
    public static function removeRoleAnnotations($existingAnnotations, string $role, ?string $username = null): array
    {
        $parsedExisting = is_array($existingAnnotations) ? $existingAnnotations : (json_decode($existingAnnotations ?? '', true) ?? []);
        
        if (!isset($parsedExisting['current']) && !isset($parsedExisting['history'])) {
            $current = [
                'current' => $parsedExisting,
                'history' => []
            ];
        } else {
            $current = $parsedExisting;
        }

        $order = ['member', 'leader', 'auditor'];
        $startIdx = array_search($role, $order);

        if ($startIdx === false) {
            return $current;
        }

        foreach (array_slice($order, $startIdx) as $r) {
            unset($current['current'][$r]);
        }

        // Append a snapshot to history reflecting the deletion
        $version = count($current['history']) + 1;
        $current['history'][] = [
            'version' => $version,
            'timestamp' => Carbon::now()->toDateTimeString(),
            'role' => $role,
            'action' => 'remove',
            'name' => $username ?? session('Username_User', 'Unknown'),
            'annotations' => $current['current']
        ];

        return $current;
    }
}

