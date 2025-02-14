<?php

namespace DigitalNode\FormMaker\Component\Traits;

trait ProcessesFields {
    private function initializeProperties($is_on_page, $is_on_term_options_page, $taxonomy): void
    {
        $this->is_on_page = $is_on_page;
        $this->is_on_term_options_page = $is_on_term_options_page;
        $this->taxonomy = $taxonomy;
    }

    private function getExistingFormData(): ?array
    {
        $data = DB::table('form_submissions')
                  ->where('form_key', $this->groupKey)
                  ->first();

        return $data ? json_decode($data->form_content, true) : null;
    }

    private function processFields($group, ?array $existingData): void
    {
        $fields = collect(apply_filters('larafields_load_forms_' . $group['name'], $group['fields']));

        $fields->each(function ($field) use ($existingData, $group) {
            $field = $this->applyFieldFilters($field, $group);
            $this->processField($field, $existingData);
        });
    }

    private function applyFieldFilters(array $field, array $group): array
    {
        return apply_filters(
            sprintf('larafields_load_forms_%s_%s', $group['name'], $field['name']),
            $field
        );
    }

    private function processField(array $field, ?array $existingData): void
    {
        $defaultValue = $existingData[$field['name']] ?? $field['defaultValue'] ?? '';
        $this->availablePropertiesData[$field['name']] = $defaultValue;

        $schemaProcessor = $this->getSchemaProcessor($field['type']);
        if ($schemaProcessor) {
            $this->availablePropertiesSchema[] = $schemaProcessor($field, $existingData);
        }
    }

    private function getSchemaProcessor(string $fieldType): ?callable
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
            'required' => $field['required'],
        ];
    }

    private function processMultiselectField(array $field, ?array $existingData): array
    {
        $this->availablePropertiesData[$field['name']] =
            $existingData[$field['name']] ?? $field['defaultValue'] ?? [];

        return array_merge($this->processBasicField($field), [
            'options' => $field['options'],
        ]);
    }

    private function processRepeaterField(array $field, ?array $existingData): array
    {
        $defaults = $this->getRepeaterDefaults($field['subfields']);

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

    private function getRepeaterDefaults(array $subfields): array
    {
        return collect($subfields)->mapWithKeys(function ($value) {
            return [$value['name'] => $value['defaultValue'] ?? ''];
        })->all();
    }
}
