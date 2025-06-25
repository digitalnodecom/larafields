<input
  wire:model="{{ $field['key'] }}"
  wire:key="{{ $field['key'] }}"
  type="text"
  name="{{ $field['name'] }}"
  @required($field['required'])
/>
