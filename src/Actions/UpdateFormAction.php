<?php

namespace DigitalNode\Larafields\Actions;

use DigitalNode\Larafields\Larafields;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UpdateFormAction
{
    protected $formMaker;

    public function __construct(Larafields $formMaker)
    {
        $this->formMaker = $formMaker;
    }


    public function execute(Request $request)
    {
        $data = $request->validate([
            'field_key' => 'required',
            'field_value' => 'required',
            'object_id' => 'required',
            'object_name' => 'required',
        ]);

        $this->formMaker->loadFormsAndPages();

        $formForUpdate = $this->findFormConfiguration($data['field_key']);
        if (!$formForUpdate) {
            return [
                'status' => 'error',
                'message' => "Form field '{$data['field_key']}' not found in any form configuration",
                'code' => 422
            ];
        }

        $fieldConfig = collect($formForUpdate['fields'])->firstWhere('name', $data['field_key']);

        $fieldValue = json_decode($data['field_value'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status' => 'error',
                'message' => 'Invalid JSON in field_value: ' . json_last_error_msg(),
                'code' => 422
            ];
        }

        $validationErrors = $this->validateFieldValue($fieldConfig, $fieldValue);
        
        if ($validationErrors->isNotEmpty()) {
            return [
                'status' => 'error',
                'message' => 'Schema validation failed',
                'errors' => $validationErrors->all(),
                'code' => 422
            ];
        }

        DB::table('larafields')
            ->where('field_key', $data['field_key'])
            ->where('object_id', $data['object_id'])
            ->where('object_name', $data['object_name'])
            ->update(['field_value' => json_encode($fieldValue)]);

        return [
            'status' => 'ok'
        ];
    }

    protected function findFormConfiguration(string $fieldKey)
    {
        return collect($this->formMaker->forms)->firstWhere(
            fn($form) => collect($form['fields'])->contains('name', $fieldKey)
        );
    }

    protected function validateFieldValue(array $fieldConfig, $fieldValue): Collection
    {
        $validationErrors = collect();

        // Handle different field types
        switch ($fieldConfig['type']) {
            case 'repeater':
                $validationErrors = $this->validateRepeaterField($fieldConfig, $fieldValue);
                break;
            
            case 'text':
            case 'textarea':
            case 'file':
                $validationErrors = $this->validateTextField($fieldConfig, $fieldValue);
                break;
            
            case 'date':
            case 'datetime':
            case 'week':
            case 'month':
                $validationErrors = $this->validateDateField($fieldConfig, $fieldValue);
                break;
            
            case 'number':
                $validationErrors = $this->validateNumberField($fieldConfig, $fieldValue);
                break;
            
            case 'select':
                $validationErrors = $this->validateSelectField($fieldConfig, $fieldValue);
                break;
            
            case 'multiselect':
                $validationErrors = $this->validateMultiselectField($fieldConfig, $fieldValue);
                break;
        }

        return $validationErrors;
    }

    /**
     * Validate a repeater field.
     *
     * @param array $fieldConfig
     * @param mixed $fieldValue
     * @return Collection
     */
    protected function validateRepeaterField(array $fieldConfig, $fieldValue): Collection
    {
        $validationErrors = collect();

        if (!is_array($fieldValue)) {
            $validationErrors->push("Field '{$fieldConfig['name']}' must be an array");
            return $validationErrors;
        }

        if (!empty($fieldValue)) {
            $subfields = collect($fieldConfig['subfields']);
            $subfieldNames = $subfields->pluck('name')->toArray();

            //$requiredSubfields = $subfields
            //    ->filter(fn($subfield) => isset($subfield['required']) && $subfield['required'] === true)
            //    ->pluck('name')
            //    ->toArray();

            collect($fieldValue)->each(function ($item, $index) use ($validationErrors, $subfieldNames, $subfields) {
                if (!is_array($item)) {
                    $validationErrors->push("Item at index $index must be an object");
                    return;
                }

                collect(array_keys($item))->each(function ($key) use ($validationErrors, $subfieldNames, $index) {
                    if (!in_array($key, $subfieldNames)) {
                        $validationErrors->push("Unknown property '$key' at index $index. Allowed properties: " . implode(', ', $subfieldNames));
                    }
                });

                //collect($requiredSubfields)->each(function ($requiredField) use ($validationErrors, $item, $index) {
                //    if (!isset($item[$requiredField]) || $item[$requiredField] === null || $item[$requiredField] === '') {
                //        $validationErrors->push("Required property '$requiredField' is missing or empty at index $index");
                //    }
                //});

                $this->validateFieldTypes($subfields, $item, $index, $validationErrors);
            });
        }

        return $validationErrors;
    }

    /**
     * Validate a text field.
     *
     * @param array $fieldConfig
     * @param mixed $fieldValue
     * @return Collection
     */
    protected function validateTextField(array $fieldConfig, $fieldValue): Collection
    {
        $validationErrors = collect();

        // Skip validation if the field is not required and the value is empty
        if (empty($fieldValue) && (!isset($fieldConfig['required']) || !$fieldConfig['required'])) {
            return $validationErrors;
        }

        // Validate that the value is a string
        if (!is_string($fieldValue) && !is_null($fieldValue)) {
            $validationErrors->push("Field '{$fieldConfig['name']}' must be a string");
        }

        // Validate character limit if specified
        if (is_string($fieldValue) && isset($fieldConfig['characterLimit']) && strlen($fieldValue) > $fieldConfig['characterLimit']) {
            $validationErrors->push("Field '{$fieldConfig['name']}' exceeds character limit of {$fieldConfig['characterLimit']}");
        }

        return $validationErrors;
    }

    /**
     * Validate a date field.
     *
     * @param array $fieldConfig
     * @param mixed $fieldValue
     * @return Collection
     */
    protected function validateDateField(array $fieldConfig, $fieldValue): Collection
    {
        $validationErrors = collect();

        // Skip validation if the field is not required and the value is empty
        if (empty($fieldValue) && (!isset($fieldConfig['required']) || !$fieldConfig['required'])) {
            return $validationErrors;
        }

        // Validate that the value is a string
        if (!is_string($fieldValue)) {
            $validationErrors->push("Field '{$fieldConfig['name']}' must be a string");
            return $validationErrors;
        }

        // Validate the format based on field type
        switch ($fieldConfig['type']) {
            case 'date':
                // Validate date format (YYYY-MM-DD)
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fieldValue)) {
                    $validationErrors->push("Field '{$fieldConfig['name']}' must be in the format YYYY-MM-DD");
                }
                break;
            
            case 'datetime':
                // Validate datetime format (YYYY-MM-DDThh:mm)
                if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/', $fieldValue)) {
                    $validationErrors->push("Field '{$fieldConfig['name']}' must be in the format YYYY-MM-DDThh:mm");
                }
                break;
            
            case 'week':
                // Validate week format (YYYY-Www)
                if (!preg_match('/^\d{4}-W\d{2}$/', $fieldValue)) {
                    $validationErrors->push("Field '{$fieldConfig['name']}' must be in the format YYYY-Www");
                }
                break;
            
            case 'month':
                // Validate month format (YYYY-MM)
                if (!preg_match('/^\d{4}-\d{2}$/', $fieldValue)) {
                    $validationErrors->push("Field '{$fieldConfig['name']}' must be in the format YYYY-MM");
                }
                break;
        }

        return $validationErrors;
    }

    /**
     * Validate a number field.
     *
     * @param array $fieldConfig
     * @param mixed $fieldValue
     * @return Collection
     */
    protected function validateNumberField(array $fieldConfig, $fieldValue): Collection
    {
        $validationErrors = collect();

        // Skip validation if the field is not required and the value is empty
        if (empty($fieldValue) && (!isset($fieldConfig['required']) || !$fieldConfig['required'])) {
            return $validationErrors;
        }

        // Validate that the value is a number
        if (!is_numeric($fieldValue)) {
            $validationErrors->push("Field '{$fieldConfig['name']}' must be a number");
            return $validationErrors;
        }

        // Convert to numeric for comparison
        $numericValue = (float) $fieldValue;

        // Validate min value if specified
        if (isset($fieldConfig['minValue']) && $numericValue < $fieldConfig['minValue']) {
            $validationErrors->push("Field '{$fieldConfig['name']}' must be greater than or equal to {$fieldConfig['minValue']}");
        }

        // Validate max value if specified
        if (isset($fieldConfig['maxValue']) && $numericValue > $fieldConfig['maxValue']) {
            $validationErrors->push("Field '{$fieldConfig['name']}' must be less than or equal to {$fieldConfig['maxValue']}");
        }

        return $validationErrors;
    }

    /**
     * Validate a select field.
     *
     * @param array $fieldConfig
     * @param mixed $fieldValue
     * @return Collection
     */
    protected function validateSelectField(array $fieldConfig, $fieldValue): Collection
    {
        $validationErrors = collect();

        // Skip validation if the field is not required and the value is empty
        if (empty($fieldValue) && (!isset($fieldConfig['required']) || !$fieldConfig['required'])) {
            return $validationErrors;
        }

        // Validate that the value is in the options list
        if (!empty($fieldValue) && isset($fieldConfig['options']) && is_array($fieldConfig['options'])) {
            $validOptions = collect($fieldConfig['options'])->pluck('value')->toArray();
            if (!in_array($fieldValue, $validOptions)) {
                $validationErrors->push("Field '{$fieldConfig['name']}' has invalid value. Allowed values: " . implode(', ', $validOptions));
            }
        }

        return $validationErrors;
    }

    /**
     * Validate a multiselect field.
     *
     * @param array $fieldConfig
     * @param mixed $fieldValue
     * @return Collection
     */
    protected function validateMultiselectField(array $fieldConfig, $fieldValue): Collection
    {
        $validationErrors = collect();

        // Skip validation if the field is not required and the value is empty
        if (empty($fieldValue) && (!isset($fieldConfig['required']) || !$fieldConfig['required'])) {
            return $validationErrors;
        }

        // Validate that the value is an array
        if (!is_array($fieldValue)) {
            $validationErrors->push("Field '{$fieldConfig['name']}' must be an array");
            return $validationErrors;
        }

        // Validate that each value is in the options list
        if (!empty($fieldValue) && isset($fieldConfig['options']) && is_array($fieldConfig['options'])) {
            $validOptions = collect($fieldConfig['options'])->pluck('value')->toArray();
            
            // If custom values are allowed, skip validation of options
            if (!isset($fieldConfig['custom_values']) || !$fieldConfig['custom_values']) {
                collect($fieldValue)->each(function ($value) use ($validationErrors, $validOptions, $fieldConfig) {
                    if (!in_array($value, $validOptions)) {
                        $validationErrors->push("Field '{$fieldConfig['name']}' contains invalid value '$value'. Allowed values: " . implode(', ', $validOptions));
                    }
                });
            }
        }

        return $validationErrors;
    }

    protected function validateFieldTypes(Collection $subfields, array $item, int $index, Collection $validationErrors): void
    {
        $subfields->each(function ($subfield) use ($validationErrors, $item, $index) {
            $fieldName = $subfield['name'];

            if (!isset($item[$fieldName])) {
                return;
            }

            $fieldValue = $item[$fieldName];
            $fieldType = $subfield['type'];

            switch ($fieldType) {
                case 'text':
                case 'file':
                case 'textarea':
                    if (!is_string($fieldValue) && !is_null($fieldValue)) {
                        $validationErrors->push("Property '$fieldName' at index $index must be a string");
                    }
                    
                    // Validate character limit if specified
                    if (is_string($fieldValue) && isset($subfield['characterLimit']) && strlen($fieldValue) > $subfield['characterLimit']) {
                        $validationErrors->push("Property '$fieldName' at index $index exceeds character limit of {$subfield['characterLimit']}");
                    }
                    break;

                case 'date':
                    // Validate date format (YYYY-MM-DD)
                    if (!is_string($fieldValue)) {
                        $validationErrors->push("Property '$fieldName' at index $index must be a string");
                    } elseif (!empty($fieldValue) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fieldValue)) {
                        $validationErrors->push("Property '$fieldName' at index $index must be in the format YYYY-MM-DD");
                    }
                    break;
                
                case 'datetime':
                    // Validate datetime format (YYYY-MM-DDThh:mm)
                    if (!is_string($fieldValue)) {
                        $validationErrors->push("Property '$fieldName' at index $index must be a string");
                    } elseif (!empty($fieldValue) && !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/', $fieldValue)) {
                        $validationErrors->push("Property '$fieldName' at index $index must be in the format YYYY-MM-DDThh:mm");
                    }
                    break;
                
                case 'week':
                    // Validate week format (YYYY-Www)
                    if (!is_string($fieldValue)) {
                        $validationErrors->push("Property '$fieldName' at index $index must be a string");
                    } elseif (!empty($fieldValue) && !preg_match('/^\d{4}-W\d{2}$/', $fieldValue)) {
                        $validationErrors->push("Property '$fieldName' at index $index must be in the format YYYY-Www");
                    }
                    break;
                
                case 'month':
                    // Validate month format (YYYY-MM)
                    if (!is_string($fieldValue)) {
                        $validationErrors->push("Property '$fieldName' at index $index must be a string");
                    } elseif (!empty($fieldValue) && !preg_match('/^\d{4}-\d{2}$/', $fieldValue)) {
                        $validationErrors->push("Property '$fieldName' at index $index must be in the format YYYY-MM");
                    }
                    break;

                case 'number':
                    // Validate that the value is a number
                    if (!is_numeric($fieldValue) && !is_null($fieldValue)) {
                        $validationErrors->push("Property '$fieldName' at index $index must be a number");
                        break;
                    }
                    
                    // Convert to numeric for comparison
                    if (is_numeric($fieldValue)) {
                        $numericValue = (float) $fieldValue;
                        
                        // Validate min value if specified
                        if (isset($subfield['minValue']) && $numericValue < $subfield['minValue']) {
                            $validationErrors->push("Property '$fieldName' at index $index must be greater than or equal to {$subfield['minValue']}");
                        }
                        
                        // Validate max value if specified
                        if (isset($subfield['maxValue']) && $numericValue > $subfield['maxValue']) {
                            $validationErrors->push("Property '$fieldName' at index $index must be less than or equal to {$subfield['maxValue']}");
                        }
                    }
                    break;

                case 'select':
                    if (!empty($fieldValue)) {
                        if (isset($subfield['options']) && is_array($subfield['options'])) {
                            $validOptions = collect($subfield['options'])->pluck('value')->toArray();
                            if (!in_array($fieldValue, $validOptions)) {
                                $validationErrors->push("Property '$fieldName' at index $index has invalid value. Allowed values: " . implode(', ', $validOptions));
                            }
                        }
                    }
                    break;

                case 'multiselect':
                    if (!is_array($fieldValue)) {
                        $validationErrors->push("Property '$fieldName' at index $index must be an array");
                    } elseif (!empty($fieldValue) && isset($subfield['options']) && is_array($subfield['options'])) {
                        $validOptions = collect($subfield['options'])->pluck('value')->toArray();
                        
                        // If custom values are allowed, skip validation of options
                        if (!isset($subfield['custom_values']) || !$subfield['custom_values']) {
                            collect($fieldValue)->each(function ($value) use ($validationErrors, $validOptions, $fieldName, $index) {
                                if (!in_array($value, $validOptions)) {
                                    $validationErrors->push("Property '$fieldName' at index $index contains invalid value '$value'. Allowed values: " . implode(', ', $validOptions));
                                }
                            });
                        }
                    }
                    break;
            }
        });
    }
}
