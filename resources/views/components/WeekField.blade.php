<input
  wire:model="{{ $field['key'] }}"
  type="week"
  name="{{ $field['name'] }}"
  @required($field['required'])
/>
