@php
  $fieldKey = $isRepeater
      ? sprintf("availablePropertiesData.dn_form_maker_%s.%s.%s", $parentField, $index, $field['name'])
      : "availablePropertiesData.dn_form_maker_" . $field['name'];

  $fieldView = match ($field['type']) {
      'text' => 'Larafields::components.TextField',
      'number' => 'Larafields::components.NumberField',
      'textarea' => 'Larafields::components.TextareaField',
      'multiselect' => 'Larafields::components.MultiselectField',
      default => null
  };
@endphp

@if($fieldView)
  <div class="form-group">
    <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
    @include($fieldView, ['fieldKey' => $fieldKey, 'field' => $field])
  </div>
@endif

@if($field['type'] == 'multiselect')
  @once
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
                $select.append(new Option(termName, termId, false, selectedValue == termId));
              });
            }
          });
        }
      });
    </script>
    @endscript
  @endonce
@endif
