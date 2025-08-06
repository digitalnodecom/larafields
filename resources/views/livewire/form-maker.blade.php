@php use Illuminate\Support\Facades\Session; @endphp
<div class="larafields lftw:space-y-4" style="max-width: 100%; box-sizing: border-box;">

  @foreach($availablePropertiesSchema as $key => $field)
    <div class="lftw:flex lftw:flex-col">
      <label class="lftw:text-lg lftw:font-semibold lftw:mb-2 lftw:text-gray-900">{{ $field['label'] }}</label>

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

  <div class="lftw:flex lftw:justify-start lftw:pt-1">
    <button
      wire:click.prevent.debounce.500ms="submit"
      wire:loading.attr="disabled"
      class="lftw:px-6 lftw:py-2.5 lftw:bg-brand-500 lftw:text-white lftw:font-medium lftw:rounded-lg lftw:hover:bg-brand-600 lftw:focus:outline-none lftw:focus:ring-2 lftw:focus:ring-brand-500 lftw:focus:ring-offset-2 lftw:disabled:opacity-50 lftw:disabled:cursor-not-allowed lftw:transition-all lftw:duration-200 lftw:shadow-sm"
    >
      <span wire:loading.remove>Submit</span>
      <span wire:loading>Saving...</span>
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
