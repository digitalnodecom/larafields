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

        foreach ($this->availablePropertiesSchema as $field) {
            if ($field['type'] === 'repeater') {
                $repeaterErrors = $this->validateRepeaterField($field);
                if (! empty($repeaterErrors)) {
                    $this->validationErrors = array_merge($this->validationErrors, $repeaterErrors);
                    $hasErrors = true;
                }
            } else {
                $fieldErrors = $this->validateSingleField($field);
                if (! empty($fieldErrors)) {
                    $this->validationErrors = array_merge($this->validationErrors, $fieldErrors);
                    $hasErrors = true;
                }
            }
        }

        return ! $hasErrors;
    }

    /**
     * Validate a single field
     */
    private function validateSingleField(array $field): array
    {
        $errors = [];
        $fieldKey = 'availablePropertiesData.'.$field['name'];
        $value = $this->availablePropertiesData[$field['name']] ?? '';

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
     * Validate a repeater field
     */
    private function validateRepeaterField(array $field): array
    {
        $errors = [];
        $repeaterData = $this->availablePropertiesData[$field['name']] ?? [];

        foreach ($repeaterData as $rowIndex => $rowData) {
            foreach ($field['subfields'] as $subfield) {
                $subfieldKey = sprintf('availablePropertiesData.%s.%s.%s', $field['name'], $rowIndex, $subfield['name']);
                $value = $rowData[$subfield['name']] ?? '';

                $rules = $this->buildValidationRules($subfield, $field, $rowIndex);

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
     * Build validation rules for a field
     */
    private function buildValidationRules(array $field, ?array $parentField = null, ?int $rowIndex = null): array
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
                    $repeaterData = $this->availablePropertiesData[$parentField['name']] ?? [];
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
            $pageList = implode(', ', array_slice($uniquePages, 0, -1)).' and '.end($uniquePages);

            return sprintf('Please fix the validation errors on pages %s before submitting.', $pageList);
        }
    }

    /**
     * Calculate which page a row index would be on
     */
    private function getPageForRowIndex(string $fieldName, int $rowIndex): int
    {
        if (! isset($this->repeaterPagination[$fieldName])) {
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
