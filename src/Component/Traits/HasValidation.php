<?php

namespace DigitalNode\Larafields\Component\Traits;

use DigitalNode\Larafields\Rules\UniqueWithinRepeater;
use Illuminate\Support\Facades\Validator;

trait HasValidation
{
    public array $validationErrors = [];

    /**
     * Validate the entire form before submission
     */
    public function validateForm(): bool
    {
        $this->validationErrors = [];
        $hasErrors = false;

        $errors = $this->validateFieldsRecursively($this->availablePropertiesSchema, '');
        if (!empty($errors)) {
            $this->validationErrors = array_merge($this->validationErrors, $errors);
            $hasErrors = true;
        }

        return ! $hasErrors;
    }

    /**
     * Validate fields recursively, supporting nested repeaters
     */
    private function validateFieldsRecursively($fields, string $parentPath = ''): array
    {
        $errors = [];
        
        // Convert Collection to array if needed
        if ($fields instanceof \Illuminate\Support\Collection) {
            $fields = $fields->toArray();
        }
        
        foreach ($fields as $field) {
            $fieldPath = $parentPath ? "{$parentPath}.{$field['name']}" : $field['name'];
            
            if ($field['type'] === 'repeater') {
                $repeaterErrors = $this->validateRepeaterFieldRecursively($field, $parentPath);
                if (!empty($repeaterErrors)) {
                    $errors = array_merge($errors, $repeaterErrors);
                }
            } else {
                $fieldErrors = $this->validateSingleFieldRecursively($field, $parentPath);
                if (!empty($fieldErrors)) {
                    $errors = array_merge($errors, $fieldErrors);
                }
            }
        }
        
        return $errors;
    }

    /**
     * Validate a single field with support for nested paths
     */
    private function validateSingleFieldRecursively(array $field, string $parentPath = ''): array
    {
        $errors = [];
        $fieldPath = $parentPath ? "{$parentPath}.{$field['name']}" : $field['name'];
        $fieldKey = "availablePropertiesData.{$fieldPath}";
        
        $value = data_get($this->availablePropertiesData, $fieldPath, '');

        $rules = $this->buildValidationRules($field);

        if (! empty($rules)) {
            $validator = Validator::make(
                [$field['name'] => $value],
                [$field['name'] => $rules]
            );

            if ($validator->fails()) {
                $errors[$fieldKey] = $validator->errors()->first($field['name']);
            }
        }

        return $errors;
    }

    /**
     * Validate a single field (legacy method for backward compatibility)
     */
    private function validateSingleField(array $field): array
    {
        return $this->validateSingleFieldRecursively($field, '');
    }

    /**
     * Validate a repeater field recursively
     */
    private function validateRepeaterFieldRecursively(array $field, string $parentPath = ''): array
    {
        $errors = [];
        $fieldPath = $parentPath ? "{$parentPath}.{$field['name']}" : $field['name'];
        $repeaterData = data_get($this->availablePropertiesData, $fieldPath, []);

        // Ensure repeaterData is an array
        if (!is_array($repeaterData)) {
            // Skip validation if the data is not in the expected format
            return $errors;
        }

        foreach ($repeaterData as $rowIndex => $rowData) {
            // Ensure rowData is an array
            if (!is_array($rowData)) {
                continue;
            }
            
            $rowPath = "{$fieldPath}.{$rowIndex}";
            
            // Recursively validate subfields
            $subfields = $field['subfields'];
            if ($subfields instanceof \Illuminate\Support\Collection) {
                $subfields = $subfields->toArray();
            }
            
            $subfieldErrors = $this->validateFieldsRecursively($subfields, $rowPath);
            if (!empty($subfieldErrors)) {
                $errors = array_merge($errors, $subfieldErrors);
            }
            
            // Also validate each subfield for repeater-specific rules (like unique_within_repeater)
            foreach ($subfields as $subfield) {
                $subfieldKey = "availablePropertiesData.{$rowPath}.{$subfield['name']}";
                $value = $rowData[$subfield['name']] ?? '';

                $rules = $this->buildValidationRules($subfield, $field, (int) $rowIndex, $fieldPath);

                if (! empty($rules)) {
                    $validator = Validator::make(
                        [$subfield['name'] => $value],
                        [$subfield['name'] => $rules]
                    );

                    if ($validator->fails()) {
                        $errors[$subfieldKey] = $validator->errors()->first($subfield['name']);
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Validate a repeater field (legacy method for backward compatibility)
     */
    private function validateRepeaterField(array $field): array
    {
        return $this->validateRepeaterFieldRecursively($field, '');
    }

    /**
     * Build validation rules for a field
     */
    private function buildValidationRules(array $field, ?array $parentField = null, ?int $rowIndex = null, ?string $repeaterPath = null): array
    {
        $rules = [];

        // Required validation
        if ($field['required'] ?? false) {
            $rules[] = 'required';
        }

        // Character limit validation
        if (isset($field['characterLimit'])) {
            $rules[] = 'max:'.$field['characterLimit'];
        }

        // Custom validation rules
        if (isset($field['validation'])) {
            $validation = $field['validation'];

            // Unique within repeater validation
            if ($validation['unique_within_repeater'] ?? false) {
                if ($parentField && $rowIndex !== null) {
                    // Use the provided repeater path or fallback to parent field name
                    $dataPath = $repeaterPath ?? $parentField['name'];
                    $repeaterData = data_get($this->availablePropertiesData, $dataPath, []);
                    $message = $validation['unique_message'] ?? 'This value already exists.';

                    $rules[] = new UniqueWithinRepeater(
                        $repeaterData,
                        $field['name'],
                        $rowIndex,
                        $message
                    );
                }
            }
        }

        return $rules;
    }

    /**
     * Clear validation errors
     */
    public function clearValidationErrors(): void
    {
        $this->validationErrors = [];
    }

    /**
     * Get validation error for a specific field
     */
    public function getValidationError(string $fieldKey): ?string
    {
        return $this->validationErrors[$fieldKey] ?? null;
    }

    /**
     * Check if a field has validation errors
     */
    public function hasValidationError(string $fieldKey): bool
    {
        return isset($this->validationErrors[$fieldKey]);
    }

    /**
     * Get validation error summary with page information
     */
    public function getValidationErrorSummary(): string
    {
        if (empty($this->validationErrors)) {
            return 'Please fix the validation errors before submitting.';
        }

        $errorPages = [];
        
        foreach ($this->validationErrors as $fieldKey => $errorMessage) {
            // Check if this is a repeater field error
            if (preg_match('/availablePropertiesData\.([^.]+)\.(\d+)\./', $fieldKey, $matches)) {
                $fieldName = $matches[1];
                $rowIndex = (int) $matches[2];
                
                // Calculate which page this row would be on
                $page = $this->getPageForRowIndex($fieldName, $rowIndex);
                
                if ($page > 0) {
                    $errorPages[] = $page;
                }
            }
        }

        if (empty($errorPages)) {
            return 'Please fix the validation errors before submitting.';
        }

        $uniquePages = array_unique($errorPages);
        sort($uniquePages);

        if (count($uniquePages) === 1) {
            return sprintf('Please fix the validation errors on page %d before submitting.', $uniquePages[0]);
        } else {
            $pageList = implode(', ', array_slice($uniquePages, 0, -1)) . ' and ' . end($uniquePages);
            return sprintf('Please fix the validation errors on pages %s before submitting.', $pageList);
        }
    }

    /**
     * Calculate which page a row index would be on
     */
    private function getPageForRowIndex(string $fieldName, int $rowIndex): int
    {
        if (!isset($this->repeaterPagination[$fieldName])) {
            return 1;
        }

        // Get filtered rows to account for search
        $filteredRows = $this->getFilteredRepeaterRows($fieldName);
        $filteredKeys = array_keys($filteredRows);
        
        // Find the position of this row index in the filtered results
        $position = array_search($rowIndex, $filteredKeys);
        
        if ($position === false) {
            return 1;
        }

        // Calculate page number (1-based)
        return (int) floor($position / $this->itemsPerPage) + 1;
    }

    /**
     * Navigate to the first page with validation errors
     */
    public function navigateToFirstErrorPage(): void
    {
        if (empty($this->validationErrors)) {
            return;
        }

        foreach ($this->validationErrors as $fieldKey => $errorMessage) {
            // Check if this is a repeater field error
            if (preg_match('/availablePropertiesData\.([^.]+)\.(\d+)\./', $fieldKey, $matches)) {
                $fieldName = $matches[1];
                $rowIndex = (int) $matches[2];
                
                // Calculate which page this row would be on
                $page = $this->getPageForRowIndex($fieldName, $rowIndex);
                
                if ($page > 0 && isset($this->repeaterPagination[$fieldName])) {
                    // Navigate to the page with the error
                    $this->changePage($fieldName, $page);
                    return; // Navigate to the first error found
                }
            }
        }
    }
}
