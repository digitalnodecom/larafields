<input
  wire:model="{{ $field['key'] }}"
  wire:key="{{ $field['key'] }}"
  type="date"
  name="{{ $field['name'] }}"
  class="w-full"
  @required($field['required'])
/>
