<input
  wire:model="{{ $field['key'] }}"
  wire:key="{{ $field['key'] }}"
  type="text"
  name="{{ $field['name'] }}"
  placeholder="{{ $field['placeholder'] ?? '' }}"
  class="lftw:w-full lftw:px-3 lftw:py-2 lftw:border lftw:border-gray-300 lftw:bg-white lftw:text-gray-900 lftw:placeholder-gray-500 lftw:focus:outline-none lftw:focus:ring-2 lftw:focus:ring-brand-500 lftw:focus:border-brand-500 lftw:transition-colors lftw:duration-200"
  @required($field['required'])
/>
