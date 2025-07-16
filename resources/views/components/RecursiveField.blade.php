@props(['field', 'fieldKey' => null, 'index' => null, 'parentFieldName' => null, 'schemaKey' => null, 'subfieldIndex' => null])

@php
    // Generate the field key if not provided
    $fieldKey = $fieldKey ?? ($parentFieldName ? 
        "availablePropertiesData.{$parentFieldName}.{$index}.{$field['name']}" : 
        "availablePropertiesData.{$field['name']}"
    );
    
    // Set the field key for wire:model binding
    $field['key'] = $fieldKey;
@endphp

<div class="recursive-field">
    @if($field['type'] == 'text')
        @include('Larafields::components.TextField', ['field' => $field])
    @elseif($field['type'] == 'number')
        @include('Larafields::components.NumberField', ['field' => $field])
    @elseif($field['type'] == 'textarea')
        @include('Larafields::components.TextareaField', ['field' => $field])
    @elseif($field['type'] == 'datetime')
        @include('Larafields::components.DateTimeField', ['field' => $field])
    @elseif($field['type'] == 'date')
        @include('Larafields::components.DateField', ['field' => $field])
    @elseif($field['type'] == 'week')
        @include('Larafields::components.WeekField', ['field' => $field])
    @elseif($field['type'] == 'month')
        @include('Larafields::components.MonthField', ['field' => $field])
    @elseif($field['type'] == 'file')
        @include('Larafields::components.FileField', ['field' => $field])
    @elseif($field['type'] == 'multiselect')
        @php
            // Generate the correct options path based on context
            if ($parentFieldName && $subfieldIndex !== null) {
                // This is a subfield in a repeater
                $optionsPath = "availablePropertiesSchema.{$schemaKey}.subfields.{$subfieldIndex}.options";
            } else {
                // This is a root field
                $optionsPath = "availablePropertiesSchema.{$schemaKey}.options";
            }
        @endphp
        <x-tom-select
            class="multiselect"
            wire:model="{!! $fieldKey !!}"
            options="{!! $optionsPath !!}"
            key="ms{{ $schemaKey }}{{ $index ?? '' }}"
            :create="($field['custom_values'] ?? false) ? true : null"
            multiple
        />
    @elseif($field['type'] == 'select')
        @php
            // Generate the correct options path based on context
            if ($parentFieldName && $subfieldIndex !== null) {
                // This is a subfield in a repeater
                $optionsPath = "availablePropertiesSchema.{$schemaKey}.subfields.{$subfieldIndex}.options";
            } else {
                // This is a root field
                $optionsPath = "availablePropertiesSchema.{$schemaKey}.options";
            }
        @endphp
        <x-tom-select
            wire:model="{!! $fieldKey !!}"
            options="{!! $optionsPath !!}"
            key="select{{ $schemaKey }}{{ $index ?? '' }}"
            :create="($field['custom_values'] ?? false) ? true : null"
            wire:ignore
        />
    @elseif($field['type'] == 'repeater')
        @include('Larafields::components.RepeaterField', [
            'field' => $field,
            'fieldKey' => $fieldKey,
            'parentFieldName' => $parentFieldName ? "{$parentFieldName}.{$index}" : null,
            'nestingLevel' => ($nestingLevel ?? 0) + 1,
            'schemaKey' => $schemaKey,
            'subfieldIndex' => $subfieldIndex
        ])
    @endif

    @if($this->hasValidationError($fieldKey))
        <div class="text-red-500 text-sm mt-1">
            {{ $this->getValidationError($fieldKey) }}
        </div>
    @endif
</div>