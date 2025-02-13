@php use Illuminate\Support\Facades\Session; @endphp
<div>
  @foreach($availablePropertiesSchema as $key => $field)
    <div class="mb-2 flex flex-col">
      <label for="story">{{ $field['label'] }}</label>

      @php
        $field['key'] = 'availablePropertiesData.dn_form_maker_' . $field['name'];
      @endphp

      @if($field['type'] == 'text')
        @include('FormMaker::components.TextField', ['field' => $field])
      @endif

      @if($field['type'] == 'number')
        @include('FormMaker::components.NumberField', ['field' => $field])
      @endif

      @if($field['type'] == 'textarea')
        @include('FormMaker::components.TextareaField', ['field' => $field])
      @endif

      @if($field['type'] == 'multiselect')
        <x-tom-select
          class="multiselect"
          wire:model="{{ sprintf('availablePropertiesData.dn_form_maker_%s', $field['name']) }}"
          options="{{ sprintf('availablePropertiesSchema.%s.options', $key) }}"
          multiple
        />
      @endif

      @if($field['type'] == 'select')
        <x-tom-select
          class="multiselect"
          wire:model="{{ sprintf('availablePropertiesData.dn_form_maker_%s', $field['name']) }}"
          options="{{ sprintf('availablePropertiesSchema.%s.options', $key) }}" multiple
        />
      @endif

      @if($field['type'] == 'repeater')
        <div class="repeater">
          @foreach($availablePropertiesData['dn_form_maker_' . $field['name']] as $index => $repeaterItem)
            <div class="repeater-row" style="display: flex; justify-content: space-between; width: 100%;">
              @foreach($field['subfields'] as $subfieldIndex => $subfield)
                @php
                  $subfield['key'] = sprintf("availablePropertiesData.dn_form_maker_%s.%s.%s", $field['name'], $index, $subfield['name']);
                @endphp
                <div>
                  <label>{{ $subfield['label'] }}</label>
                  @if($subfield['type'] == 'text')
                    @include('FormMaker::components.TextField', ['field' => $subfield])
                  @endif

                  @if($subfield['type'] == 'number')
                    @include('FormMaker::components.NumberField', ['field' => $subfield])
                  @endif

                  @if($subfield['type'] == 'textarea')
                    @include('FormMaker::components.TextareaField', ['field' => $subfield])
                  @endif

                  @if($subfield['type'] == 'multiselect')
                    <x-tom-select
                      class="multiselect"
                      wire:model="{{ sprintf('availablePropertiesData.dn_form_maker_%s.%s.%s', $field['name'], $index, $subfield['name']) }}"
                      options="{{ sprintf('availablePropertiesSchema.%s.subfields.%s.options', $key, $subfieldIndex) }}"
                      key="ms{{$index}}"
                      wire:ignore
                      multiple
                    />
                  @endif

                  @if($subfield['type'] == 'select')
                    <x-tom-select
                      wire:model="{{ sprintf('availablePropertiesData.dn_form_maker_%s.%s.%s', $field['name'], $index, $subfield['name']) }}"
                      options="{{ sprintf('availablePropertiesSchema.%s.subfields.%s.options', $key, $subfieldIndex) }}"
                      key="ms{{$index}}"
                      wire:ignore
                    />
                  @endif
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
