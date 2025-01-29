<textarea
    wire:model="{{ $field['key'] }}"
    name="{{ $field['name'] }}"
    cols="10"
    @required($field['required'])
></textarea>
