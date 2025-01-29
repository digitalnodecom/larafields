<x-tom-select
  class="multiselect"
  wire:model="{{ $model }}"
  options="{{ json_encode($options) }}"
  multiple
/>
