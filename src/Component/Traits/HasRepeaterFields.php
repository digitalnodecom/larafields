<?php

namespace DigitalNode\Larafields\Component\Traits;

trait HasRepeaterFields
{
    public function addRepeaterRow($fieldName)
    {
        $field = collect($this->availablePropertiesSchema)->firstWhere('name', $fieldName);

        $defaults = collect($field['subfields'])->mapWithKeys(function ($value) {
            return [$value['name'] => $value['defaultValue'] ?? ''];
        })->all();

        $this->availablePropertiesData[$fieldName][] = $defaults;
        
        // Update pagination after adding a row
        $this->updateRepeaterPagination($fieldName);
        
        // Navigate to the last page to show the newly added row
        if (isset($this->repeaterPagination[$fieldName])) {
            $this->repeaterPagination[$fieldName]['currentPage'] = $this->repeaterPagination[$fieldName]['totalPages'];
        }
    }

    public function removeRepeaterRow($fieldName, $index)
    {
        unset($this->availablePropertiesData[$fieldName][$index]);
        $this->availablePropertiesData[$fieldName] = array_values($this->availablePropertiesData[$fieldName]);
        
        // Update pagination after removing a row
        $this->updateRepeaterPagination($fieldName);
    }
}
