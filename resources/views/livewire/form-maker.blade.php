@php use Illuminate\Support\Facades\Session; @endphp
<div class="space-y-8">
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

  <button
    wire:click.prevent.debounce.500ms="submit"
    wire:loading.attr="disabled"
    class="submit-btn"
  >
    <span wire:loading.remove>Submit</span>
    <span wire:loading>Saving...</span>
  </button>
</div>
