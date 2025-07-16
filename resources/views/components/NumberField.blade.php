<input
  wire:model="{{ $field['key'] }}"
  wire:key="{{ $field['key'] }}"
  type="number"
  name="{{ $field['name'] }}"
  class="w-full"
  @required($field['required'])
/>
