<div wire:ignore>
  <select
    x-data="{
                tomSelect: null,
                options: @entangle($attributes['options']),
                selectValue: @entangle($attributes->whereStartsWith('wire:model')->first()),
                updateOptions() {
                    if (!this.tomSelect) return;
                    this.tomSelect.clearOptions();
                    this.tomSelect.addOptions(this.options);
                    this.tomSelect.settings.placeholder = '-- SELECT --';
                    this.tomSelect.inputState();
                }
            }"
    x-init="
                tomSelect = new TomSelect($refs.{{$attributes->get('key')}}, {
                    options: options,
                    items: selectValue,
                    valueField: 'value',
                    labelField: 'label',
                    searchField: 'label',
                    plugins: ['remove_button']
                });

                $watch('selectValue', (newValue) => {
                    console.log(newValue);
                    $wire.$set('{{ $attributes->whereStartsWith('wire:model')->first() }}', newValue, false)
                    if (newValue === null && tomSelect) {
                        tomSelect.clear(true);
                    }
                });"
    x-ref="{{ $attributes->get('key')  }}"
    x-cloak
    {{ $attributes }}>
  </select>
</div>
