<textarea
    wire:model="{{ $field['key'] }}"
    wire:key="{{ $field['key'] }}"
    name="{{ $field['name'] }}"
    class="w-full"
    cols="10"
    @required($field['required'])
></textarea>
