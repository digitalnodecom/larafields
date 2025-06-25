<input
  wire:model="{{ $field['key'] }}"
  wire:key="{{ $field['key'] }}"
  type="month"
  name="{{ $field['name'] }}"
  @required($field['required'])
/>
