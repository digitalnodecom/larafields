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

        if ($fieldConfig['type'] === 'repeater' && is_array($fieldValue)) {
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
                    if (!is_string($fieldValue) && !is_null($fieldValue)) {
                        $validationErrors->push("Property '$fieldName' at index $index must be a string");
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
                        
                        collect($fieldValue)->each(function ($value) use ($validationErrors, $validOptions, $fieldName, $index) {
                            if (!in_array($value, $validOptions)) {
                                $validationErrors->push("Property '$fieldName' at index $index contains invalid value '$value'. Allowed values: " . implode(', ', $validOptions));
                            }
                        });
                    }
                    break;
            }
        });
    }
}
