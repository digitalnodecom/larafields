@php use Illuminate\Support\Facades\Session; @endphp
<div class="space-y-8">
  @foreach($availablePropertiesSchema as $key => $field)
    <div class="flex flex-col">
      <label class="text-xl font-semibold mb-3">{{ $field['label'] }}</label>

      @php
        $field['key'] = 'availablePropertiesData.' . $field['name'];
      @endphp

      @if($field['type'] == 'text')
        @include('Larafields::components.TextField', ['field' => $field])
      @endif

      @if($field['type'] == 'number')
        @include('Larafields::components.NumberField', ['field' => $field])
      @endif

      @if($field['type'] == 'textarea')
        @include('Larafields::components.TextareaField', ['field' => $field])
      @endif

      @if($field['type'] == 'datetime')
        @include('Larafields::components.DateTimeField', ['field' => $field])
      @endif

      @if($field['type'] == 'date')
        @include('Larafields::components.DateField', ['field' => $field])
      @endif

      @if($field['type'] == 'week')
        @include('Larafields::components.WeekField', ['field' => $field])
      @endif

      @if($field['type'] == 'month')
        @include('Larafields::components.MonthField', ['field' => $field])
      @endif

      @if($field['type'] == 'multiselect')
        <x-tom-select
          class="multiselect"
          wire:model="{{ sprintf('availablePropertiesData.%s', $field['name']) }}"
          options="{{ sprintf('availablePropertiesSchema.%s.options', $key) }}"
          multiple
        />
      @endif

      @if($field['type'] == 'select')
        <x-tom-select
          class="multiselect"
          wire:model="{{ sprintf('availablePropertiesData.%s', $field['name']) }}"
          options="{{ sprintf('availablePropertiesSchema.%s.options', $key) }}" multiple
        />
      @endif

      @if($field['type'] == 'repeater')
        <div class="repeater">
          <table class="w-full border-collapse border border-gray-300 mb-4">
            <thead>
            <tr>
              @foreach($field['subfields'] as $subfield)
                <th class="border border-gray-300 p-2 bg-gray-100">{{ $subfield['label'] }}</th>
              @endforeach
              <th class="border border-gray-300 p-2 bg-gray-100">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($availablePropertiesData['' . $field['name']] as $index => $repeaterItem)
              <tr class="repeater-row">
                @foreach($field['subfields'] as $subfieldIndex => $subfield)
                  @php
                    $subfield['key'] = sprintf("availablePropertiesData.%s.%s.%s", $field['name'], $index, $subfield['name']);
                  @endphp
                  <td class="border border-gray-300 p-2">
                    @if($subfield['type'] == 'text')
                      @include('Larafields::components.TextField', ['field' => $subfield])
                    @endif

                    @if($subfield['type'] == 'number')
                      @include('Larafields::components.NumberField', ['field' => $subfield])
                    @endif

                    @if($subfield['type'] == 'textarea')
                      @include('Larafields::components.TextareaField', ['field' => $subfield])
                    @endif

                    @if($subfield['type'] == 'datetime')
                      @include('Larafields::components.DateTimeField', ['field' => $subfield])
                    @endif

                    @if($subfield['type'] == 'date')
                      @include('Larafields::components.DateField', ['field' => $subfield])
                    @endif

                    @if($subfield['type'] == 'week')
                      @include('Larafields::components.WeekField', ['field' => $subfield])
                    @endif

                    @if($subfield['type'] == 'month')
                      @include('Larafields::components.MonthField', ['field' => $subfield])
                    @endif

                    @if($subfield['type'] == 'multiselect')
                        <x-tom-select
                          class="multiselect"
                          wire:model="{{ sprintf('availablePropertiesData.%s.%s.%s', $field['name'], $index, $subfield['name']) }}"
                          options="{{ sprintf('availablePropertiesSchema.%s.subfields.%s.options', $key, $subfieldIndex) }}"
                          key="ms{{$index}}"
                          multiple
                        />
                    @endif

                    @if($subfield['type'] == 'select')
                      <x-tom-select
                        wire:model="{{ sprintf('availablePropertiesData.%s.%s.%s', $field['name'], $index, $subfield['name']) }}"
                        options="{{ sprintf('availablePropertiesSchema.%s.subfields.%s.options', $key, $subfieldIndex) }}"
                        key="ms{{$index}}"
                        wire:ignore
                      />
                    @endif
                  </td>
                @endforeach
                <td class="border border-gray-300 p-2 text-center">
                  <button
                    wire:click.prevent="removeRepeaterRow('{{ $field['name'] }}', {{ $index }})"
                    class="text-red-500 hover:text-red-700">
                    Remove
                  </button>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
          <button wire:click.prevent="addRepeaterRow('{{ $field['name'] }}')"
                  class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Row
          </button>
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
