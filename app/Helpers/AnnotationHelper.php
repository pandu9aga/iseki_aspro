<?php

namespace App\Helpers;

class AnnotationHelper
{
    /**
     * Save/replace annotations for a specific role.
     *
     * @param mixed $existingAnnotations
     * @param string $role 'member'|'leader'|'auditor'
     * @param mixed $newAnnotations array or json string of annotation objects
     * @return array
     */
    public static function saveRoleAnnotations($existingAnnotations, string $role, $newAnnotations): array
    {
        $current = is_array($existingAnnotations) ? $existingAnnotations : (json_decode($existingAnnotations ?? '', true) ?? []);

        if (is_string($newAnnotations)) {
            $parsed = json_decode($newAnnotations, true);
            $items = is_array($parsed) ? $parsed : [];
        } elseif (is_array($newAnnotations)) {
            $items = $newAnnotations;
        } else {
            $items = [];
        }

        $current[$role] = array_values($items);
        return $current;
    }

    /**
     * Remove annotations for a specific role (and subsequent roles in approval order).
     *
     * @param mixed $existingAnnotations
     * @param string $role
     * @return array
     */
    public static function removeRoleAnnotations($existingAnnotations, string $role): array
    {
        $current = is_array($existingAnnotations) ? $existingAnnotations : (json_decode($existingAnnotations ?? '', true) ?? []);
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
