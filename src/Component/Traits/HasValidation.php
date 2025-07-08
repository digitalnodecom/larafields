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
}
