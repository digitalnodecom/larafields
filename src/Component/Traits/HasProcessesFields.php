<?php

namespace DigitalNode\Larafields\Component\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

trait HasProcessesFields
{
    private function initializeContextProperties(
        ?string $pageContext = null,
        ?string $termOptionsContext = null,
        ?string $taxonomyContext = null,
        ?string $userContext = null
    ): void {
        $this->pageContext = $pageContext;
        $this->termOptionsContext = $termOptionsContext;
        $this->taxonomyContext = $taxonomyContext;
        $this->userContext = $userContext;
    }

    private function fetchExistingFormData(): ?array
    {
        $submission = DB::table('larafields')
            ->where('form_key', $this->groupKey)
            ->first();

        return $submission ? json_decode($submission->form_content, true) : null;
    }

    private function processFormFields(array $group, ?array $existingData): void
    {
        $fields = $this->getGroupFields($group);

        $fields->each(function ($field) use ($existingData, $group) {
            $field = $this->getGroupIndividualField($field, $group);
            $this->processGroupIndividualField($field, $existingData);
        });
    }

    private function getGroupFields(array $group): Collection
    {
        return collect(
            apply_filters('larafields_load_forms_' . $group['name'], $group['fields'])
        );
    }

    private function getGroupIndividualField(array $field, array $group): array
    {
        return apply_filters(
            sprintf('larafields_load_forms_%s_%s', $group['name'], $field['name']),
            $field
        );
    }

    private function processGroupIndividualField(array $field, ?array $existingData): void
    {
        $defaultValue = $this->determineFieldDefaultValue($field, $existingData);
        $this->availablePropertiesData[$field['name']] = $defaultValue;

        $schemaProcessor = $this->getFieldSchemaProcessor($field['type']);
        if ($schemaProcessor) {
            $this->availablePropertiesSchema[] = $schemaProcessor($field, $existingData);
        }
    }

    private function determineFieldDefaultValue(array $field, ?array $existingData)
    {
        return $existingData[$field['name']] ?? $field['defaultValue'] ?? '';
    }

    private function getFieldSchemaProcessor(string $fieldType): ?callable
    {
        $processors = [
            'text' => [$this, 'processBasicField'],
            'textarea' => [$this, 'processBasicField'],
            'number' => [$this, 'processBasicField'],
            'multiselect' => [$this, 'processMultiselectField'],
            'repeater' => [$this, 'processRepeaterField'],
        ];

        return $processors[$fieldType] ?? null;
    }

    private function processBasicField(array $field): array
    {
        return [
            'type' => $field['type'],
            'name' => $field['name'],
            'label' => $field['label'],
            'required' => $field['required'] ?? false,
        ];
    }

    private function processMultiselectField(array $field, ?array $existingData): array
    {
        $this->availablePropertiesData[$field['name']] =
            $existingData[$field['name']] ?? $field['defaultValue'] ?? [];

        return array_merge($this->processBasicField($field), [
            'options' => $field['options'] ?? [],
        ]);
    }

    private function processRepeaterField(array $field, ?array $existingData): array
    {
        $defaults = $this->generateRepeaterDefaults($field['subfields']);

        $this->availablePropertiesData[$field['name']] = collect($existingData[$field['name']] ?? [])
            ->map(fn($data) => array_merge($defaults, $data))
            ->toArray();

        return [
            'type' => 'repeater',
            'name' => $field['name'],
            'label' => $field['label'],
            'subfields' => $field['subfields'],
        ];
    }

    private function generateRepeaterDefaults(array $subfields): array
    {
        return collect($subfields)
            ->mapWithKeys(fn($field) => [$field['name'] => $field['defaultValue'] ?? ''])
            ->all();
    }
}
