@php use Illuminate\Support\Facades\Session; @endphp
<div class="larafields lftw:space-y-6" style="max-width: 100%; overflow-x: auto; box-sizing: border-box;">
  @foreach($availablePropertiesSchema as $key => $field)
    <div class="lftw:flex lftw:flex-col">
      <label class="lftw:text-lg lftw:font-semibold lftw:mb-4 lftw:text-gray-900">{{ $field['label'] }}</label>

      @php
        $field['key'] = 'availablePropertiesData.' . $field['name'];
      @endphp

      @include('Larafields::components.RecursiveField', ['field' => $field, 'schemaKey' => $key])

    </div>
  @endforeach

  @if(Session::has('message'))
    <div class="lftw:border-t lftw:border-gray-200 lftw:pt-4 lftw:mt-6">
      <p class="lftw:text-sm lftw:text-gray-600">{{ session('message') }}</p>
    </div>
  @endif

  <div class="lftw:flex lftw:flex-col lftw:sm:flex-row lftw:gap-3 lftw:items-start lftw:sm:items-center lftw:pt-4 lftw:border-t lftw:border-gray-200">
    <button
      wire:click.prevent.debounce.500ms="submit"
      wire:loading.attr="disabled"
      class="lftw:px-6 lftw:py-2 lftw:bg-brand-500 lftw:text-white lftw:font-medium lftw:hover:bg-brand-600 lftw:focus:outline-none lftw:focus:ring-2 lftw:focus:ring-brand-500 lftw:focus:ring-offset-2 lftw:disabled:opacity-50 lftw:disabled:cursor-not-allowed lftw:transition-colors lftw:duration-200"
    >
      <span wire:loading.remove>Submit</span>
      <span wire:loading>Saving...</span>
    </button>

    <button
      wire:click.prevent="exportCsv"
      wire:loading.attr="disabled"
      class="lftw:px-4 lftw:py-2 lftw:bg-gray-600 lftw:text-white lftw:font-medium lftw:hover:bg-gray-700 lftw:focus:outline-none lftw:focus:ring-2 lftw:focus:ring-gray-500 lftw:focus:ring-offset-2 lftw:disabled:opacity-50 lftw:disabled:cursor-not-allowed lftw:transition-colors lftw:duration-200 lftw:flex lftw:items-center lftw:gap-2 lftw:whitespace-nowrap"
      title="Download form data as CSV"
    >
      <svg class="lftw:w-4 lftw:h-4 lftw:flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <span wire:loading.remove wire:target="exportCsv">Download CSV</span>
      <span wire:loading wire:target="exportCsv" class="lftw:flex lftw:items-center lftw:gap-2">
        <svg class="lftw:w-4 lftw:h-4 lftw:animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="lftw:opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="lftw:opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Generating...
      </span>
    </button>
  </div>
</div>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('scroll-to-top', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>
