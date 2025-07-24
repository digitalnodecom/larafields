@php use Illuminate\Support\Facades\Session; @endphp
<div class="space-y-8" style="max-width: 100%; overflow-x: auto; box-sizing: border-box; margin-right: 16px;">
  @foreach($availablePropertiesSchema as $key => $field)
    <div class="flex flex-col">
      <label class="text-xl font-semibold mb-3">{{ $field['label'] }}</label>

      @php
        $field['key'] = 'availablePropertiesData.' . $field['name'];
      @endphp

      @include('Larafields::components.RecursiveField', ['field' => $field, 'schemaKey' => $key])

    </div>
  @endforeach

  @if(Session::has('message'))
    <hr>
    <p>{{ session('message') }}</p>
  @endif

  <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
    <button
      wire:click.prevent.debounce.500ms="submit"
      wire:loading.attr="disabled"
      class="submit-btn"
    >
      <span wire:loading.remove>Submit</span>
      <span wire:loading>Saving...</span>
    </button>

    <button
      wire:click.prevent="exportCsv"
      wire:loading.attr="disabled"
      class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
      title="Download form data as CSV"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <span wire:loading.remove wire:target="exportCsv">Download CSV</span>
      <span wire:loading wire:target="exportCsv">Generating...</span>
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
