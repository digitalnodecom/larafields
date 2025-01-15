<div>
  @foreach($availablePropertiesSchema as $field)
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

    </div>
  @endforeach

  <button wire:click="submit">Submit</button>
</div>
