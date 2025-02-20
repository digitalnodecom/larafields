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
    }

    public function removeRepeaterRow($fieldName, $index)
    {
        unset($this->availablePropertiesData[$fieldName][$index]);
        $this->availablePropertiesData[$fieldName] = array_values($this->availablePropertiesData[$fieldName]);
    }
}
