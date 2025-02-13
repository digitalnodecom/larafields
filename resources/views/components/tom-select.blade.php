<div wire:ignore>
  <select
    x-data="{
                tomSelect: null,
                options: @entangle($attributes['options']),
                selectValue: @entangle($attributes->whereStartsWith('wire:model')->first()),
                getCleanOptions() {
                    return Array.isArray(this.options)
                        ? this.options.map(opt => ({
                            value: opt.value,
                            label: opt.label
                          }))
                        : [];
                }
            }"
    x-init="
                tomSelect = new TomSelect($refs.{{$attributes->get('key')}}, {
                    options: getCleanOptions(),
                    items: selectValue || [],
                    valueField: 'value',
                    labelField: 'label',
                    searchField: 'label',
                    plugins: ['remove_button'],
                    onChange: function(value) {
                        $wire.$set('{{ $attributes->whereStartsWith('wire:model')->first() }}', value, false);
                    },
                    onFocus: function() {
                        this.addOptions(getCleanOptions());
                    }
                });

                $watch('selectValue', (newValue) => {
                    if (!tomSelect) return;

                    if (newValue === null) {
                        tomSelect.clear(true);
                    } else if (newValue !== tomSelect.getValue()) {
                        tomSelect.setValue(newValue);
                    }
                });

                $el._x_removeModelListeners = () => {
                    if (tomSelect) {
                        tomSelect.destroy();
                    }
                };"
    x-ref="{{ $attributes->get('key')  }}"
    x-cloak
    {{ $attributes }}>
  </select>
</div>
