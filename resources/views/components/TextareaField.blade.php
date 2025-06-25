<textarea
    wire:model="{{ $field['key'] }}"
    wire:key="{{ $field['key'] }}"
    name="{{ $field['name'] }}"
    cols="10"
    @required($field['required'])
></textarea>
