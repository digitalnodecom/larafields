<input
  wire:model="{{ $field['key'] }}"
  wire:key="{{ $field['key'] }}"
  type="week"
  name="{{ $field['name'] }}"
  class="w-full"
  @required($field['required'])
/>
