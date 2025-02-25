<input
  wire:model="{{ $field['key'] }}"
  type="date"
  name="{{ $field['name'] }}"
  @required($field['required'])
/>
