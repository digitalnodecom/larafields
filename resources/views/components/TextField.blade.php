<input
  wire:model="{{ $field['key'] }}"
  type="text"
  name="{{ $field['name'] }}"
  @required($field['required'])
/>
