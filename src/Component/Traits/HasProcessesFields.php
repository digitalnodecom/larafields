<?php

namespace DigitalNode\Larafields\Component\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    private function fetchExistingFormData(array $group): ?array
    {
        $fields = $this->getGroupFields($group)->map(fn ($field) => $field['name']);

        $submissions = DB::table('larafields')
            ->whereIn('field_key', $fields)
            ->where('object_id', $this->groupObjectId)
            ->get()
            ->mapWithKeys(function ($row) {
                $data = (array) $row;

                if (json_validate($row->field_value)) {
                    $data['field_value'] = json_decode($data['field_value'], true);
                }

                return [$data['field_key'] => $data];
            });

        return $submissions->all();
    }

    private function processFormFields(array $group, ?array $existingData): void
    {
        $fields = $this->getGroupFields($group);

        $fields->each(function ($field) use ($existingData, $group) {
            if ($field['type'] == 'multiselect' && isset($existingData[$field['name']]['field_value'])) {
                $existingData[$field['name']]['field_value'] = collect($existingData[$field['name']]['field_value'])->map(fn ($row) => json_encode($row))->toArray();
            }

            $field = $this->getGroupIndividualField($field, $group);
            $this->processGroupIndividualField($field, $existingData);
        });
    }

    private function getGroupFields(array $group): Collection
    {
        return collect(
            apply_filters('larafields_load_forms_'.$group['name'], $group['fields'])
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
        return $existingData[$field['name']]['field_value'] ?? $field['defaultValue'] ?? '';
    }

    private function getFieldSchemaProcessor(string $fieldType): ?callable
    {
        $processors = [
            'text' => [$this, 'processBasicField'],
            'textarea' => [$this, 'processBasicField'],
            'number' => [$this, 'processBasicField'],
            'date' => [$this, 'processBasicField'],
            'datetime' => [$this, 'processBasicField'],
            'week' => [$this, 'processBasicField'],
            'month' => [$this, 'processBasicField'],
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
            $existingData[$field['name']]['field_value'] ?? $field['defaultValue'] ?? [];

        return array_merge($this->processBasicField($field), [
            'options' => $field['options'] ?? [],
        ]);
    }

    private function processRepeaterField(array $field, ?array $existingData): array
    {
        $defaults = $this->generateRepeaterDefaults($field['subfields']);

        $this->availablePropertiesData[$field['name']] = collect($existingData[$field['name']]['field_value'] ?? [])
            ->map(function ($data) use ($field) {
                return collect($data)->map(function ($value, $field_key) use ($field) {
                    $subfield = collect($field['subfields'])->firstWhere('name', $field_key);

                    if (! $subfield) {
                        return $value;
                    }

                    if ($subfield['type'] == 'multiselect' && is_array($value)) {
                        return collect($value)->map(fn ($attr) => json_encode($attr))->toArray();
                    }

                    if ($subfield['type'] == 'select' && is_array($value)) {
                        return json_encode($value);
                    }

                    return $value;
                })->toArray();
            })
            ->map(function ($data) use ($defaults) {
                return array_merge($defaults, $data);
            })
            ->toArray();

        $field['subfields'] = collect($field['subfields'])->map(function ($subfield) {
            if (! isset($subfield['options'])) {
                $subfield['options'] = [];
            }

            return $subfield;
        });

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
            ->mapWithKeys(fn ($field) => [$field['name'] => $field['defaultValue'] ?? ''])
            ->all();
    }
}
