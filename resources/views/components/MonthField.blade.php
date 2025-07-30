<input
  wire:model="{{ $field['key'] }}"
  wire:key="{{ $field['key'] }}"
  type="month"
  name="{{ $field['name'] }}"
  class="lftw-w-full"
  @required($field['required'])
/>
