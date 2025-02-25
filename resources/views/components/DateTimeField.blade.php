<input
  wire:model="{{ $field['key'] }}"
  type="datetime-local"
  name="{{ $field['name'] }}"
  @required($field['required'])
/>
