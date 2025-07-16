<input
  wire:model="{{ $field['key'] }}"
  wire:key="{{ $field['key'] }}"
  type="text"
  name="{{ $field['name'] }}"
  size="{{ max(1, strlen(data_get($this, $field['key'], '') ?: ($field['placeholder'] ?? ''))) }}"
  @required($field['required'])
/>
