<input
  wire:model="{{ $field['key'] }}"
  type="number"
  name="{{ $field['name'] }}"
  @required($field['required'])
/>
