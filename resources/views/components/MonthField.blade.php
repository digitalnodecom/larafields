<input
  wire:model="{{ $field['key'] }}"
  type="month"
  name="{{ $field['name'] }}"
  @required($field['required'])
/>
