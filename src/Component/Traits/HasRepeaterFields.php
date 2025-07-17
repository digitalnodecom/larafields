<?php

namespace DigitalNode\Larafields\Component\Traits;

trait HasRepeaterFields
{
    public function addRepeaterRow($fieldName)
    {
        $field = $this->findRepeaterField($fieldName);

        if (! $field) {
            return;
        }

        $defaults = $this->getRepeaterDefaultValues($field['subfields']);

        // Handle nested repeater paths by navigating to the exact path
        $pathSegments = explode('.', $fieldName);
        $currentData = &$this->availablePropertiesData;

        // Navigate to the parent of the target array
        for ($i = 0; $i < count($pathSegments); $i++) {
            $segment = $pathSegments[$i];

            if (! isset($currentData[$segment])) {
                $currentData[$segment] = [];
            }

            // If this is the last segment, we're at the target array
            if ($i === count($pathSegments) - 1) {
                // Ensure it's an array before adding
                if (! is_array($currentData[$segment])) {
                    $currentData[$segment] = [];
                }
                $currentData[$segment][] = $defaults;
                break;
            }

            $currentData = &$currentData[$segment];
        }

        // Update pagination after adding a row
        $this->updateRepeaterPagination($fieldName);

        // Navigate to the last page to show the newly added row
        if (isset($this->repeaterPagination[$fieldName])) {
            $this->repeaterPagination[$fieldName]['currentPage'] = $this->repeaterPagination[$fieldName]['totalPages'];
        }
    }

    public function removeRepeaterRow($fieldName, $index)
    {
        // Handle nested repeater paths
        $currentData = &$this->availablePropertiesData;
        $pathSegments = explode('.', $fieldName);

        foreach ($pathSegments as $segment) {
            if (! isset($currentData[$segment])) {
                return;
            }
            $currentData = &$currentData[$segment];
        }

        unset($currentData[$index]);
        $currentData = array_values($currentData);

        // Update the reference back to the original data structure
        $this->setNestedValue($this->availablePropertiesData, $fieldName, $currentData);

        // Update pagination after removing a row
        $this->updateRepeaterPagination($fieldName);
    }

    /**
     * Find a repeater field by name, supporting nested paths
     */
    private function findRepeaterField($fieldName)
    {
        $pathSegments = explode('.', $fieldName);
        $lastSegment = array_pop($pathSegments);

        // Start from root schema
        $currentSchema = $this->availablePropertiesSchema;

        // Navigate through nested structure, skipping numeric indices
        foreach ($pathSegments as $segment) {
            // Skip numeric indices (row indices)
            if (is_numeric($segment)) {
                continue;
            }

            $field = collect($currentSchema)->firstWhere('name', $segment);
            if (! $field || $field['type'] !== 'repeater') {
                return null;
            }
            $currentSchema = $field['subfields'];
        }

        // Find the final field
        return collect($currentSchema)->firstWhere('name', $lastSegment);
    }

    /**
     * Get default values for repeater subfields, handling nested repeaters
     */
    private function getRepeaterDefaultValues($subfields)
    {
        return collect($subfields)->mapWithKeys(function ($field) {
            $defaultValue = $field['defaultValue'] ?? '';

            if ($field['type'] === 'repeater') {
                $defaultValue = [];
            }

            return [$field['name'] => $defaultValue];
        })->all();
    }

    /**
     * Set a nested value in an array using dot notation
     */
    private function setNestedValue(&$array, $path, $value)
    {
        $keys = explode('.', $path);
        $current = &$array;

        foreach ($keys as $key) {
            $current = &$current[$key];
        }

        $current = $value;
    }
}
