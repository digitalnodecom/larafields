@php use Illuminate\Support\Facades\Session; @endphp
<div>
  @foreach($availablePropertiesSchema as $key => $field)
    <div class="mb-2">
      <label for="story">{{ $field['label'] }}</label>

      @php
        $field['key'] = 'availablePropertiesData.dn_form_maker_' . $field['name'];

      @endphp

      @if($field['type'] == 'text')
        <input
          wire:model="{{ $field['key'] }}"
          type="text"
          name="{{ $field['name'] }}"
          @required($field['required'])
        />
      @endif

      @if($field['type'] == 'number')
        <input
          wire:model="{{ $field['key'] }}"
          type="number"
          name="{{ $field['name'] }}"
          @required($field['required'])
        />
      @endif

      @if($field['type'] == 'textarea')
        <textarea
          wire:model="{{ $field['key'] }}"
          name="{{ $field['name'] }}"
          cols="10"
            @required($field['required'])
          >
          </textarea>
      @endif

      @if($field['type'] == 'multiselect')
          <x-tom-select
            class="multiselect"
            wire:model="{{ sprintf('availablePropertiesData.dn_form_maker_%s', $field['name']) }}"
            options="{{ sprintf('availablePropertiesSchema.%s.options', $key) }}" multiple />
      @endif

      @if($field['type'] == 'repeater')
        <div class="repeater">
          @foreach($availablePropertiesData['dn_form_maker_' . $field['name']] as $index => $repeaterItem)
            <div class="repeater-row">
              @foreach($field['subfields'] as $subfield)
                @php
                  $subfieldKey = sprintf("availablePropertiesData.dn_form_maker_%s.%s.%s", $field['name'], $index, $subfield['name']);
                @endphp
                <div>
                  <label>{{ $subfield['label'] }}</label>
                  <input wire:model="{{ $subfieldKey }}" type="text" name="{{ $subfield['name'] }}" @required($subfield['required']) />
                </div>
              @endforeach
              <button wire:click.prevent="removeRepeaterRow('{{ $field['name'] }}', {{ $index }})">Remove</button>
            </div>
          @endforeach
          <button wire:click.prevent="addRepeaterRow('{{ $field['name'] }}')">Add Row</button>
        </div>
      @endif

    </div>
  @endforeach

  @if(Session::has('message'))
    <hr>
    <p>{{ session('message') }}</p>
  @endif

  <button wire:click.prevent="submit">Submit</button>
</div>

@script
<script>
  jQuery(document).ready(function () {
    initProductGroupSelects();

    Livewire.on('form-submitted', function(){
      initProductGroupSelects();
    });

    function initProductGroupSelects() {
      jQuery('select.multiselect').each(function() {
        const $select = jQuery(this);
        const selectedValue = $select.data('value');

        if ($select.find('option').length <= 1) {
          Object.entries(window.productGroups).forEach(([termId, termName]) => {
            $select.append(
              new Option(termName, termId, false, selectedValue == termId)
            );
          });
        }
      });
    }
  });
</script>
@endscript
