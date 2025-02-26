<div>
  <select
    x-data="{
                tomSelect: null,
                options: @entangle($attributes['options']),
                selectValue: @entangle($attributes->whereStartsWith('wire:model')->first()),
                getCleanOptions() {
                    let baseOptions = Array.isArray(this.options)
                        ? this.options.map(opt => ({
                            value: opt.value,
                            label: opt.label
                          }))
                        : [];

                    if (Array.isArray(this.selectValue)) {
                        this.selectValue.forEach(val => {
                            const exists = baseOptions.some(opt => opt.value === val);
                            if (!exists) {
                                baseOptions.push({
                                    value: val,
                                    label: val
                                });
                            }
                        });
                    }

                    return baseOptions;
                },
                destroy(){
                  if ( this.tomSelect ){
                    this.tomSelect.destroy();
                  }
                }
            }"
    x-init="
                tomSelect = new TomSelect($refs.{{$attributes->get('key')}}, {
                    options: getCleanOptions(),
                    {{ $attributes->get('create') ? 'create: true,' : false }}
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
                        if (Array.isArray(newValue)) {
                            newValue.forEach(val => {
                                const existingOption = tomSelect.options[val];
                                if (!existingOption) {
                                    tomSelect.addOption({value: val, label: val});
                                }
                            });
                        }

                        tomSelect.setValue(newValue);
                    }
                });

                $watch('options', () => {
                    if (!tomSelect) return;

                    const currentValues = tomSelect.getValue();
                    tomSelect.clearOptions();
                    tomSelect.addOptions(getCleanOptions());
                    tomSelect.setValue(currentValues);
                });
              ;"
    x-ref="{{ $attributes->get('key')  }}"
    x-cloak
    {{ $attributes }}>
  </select>
</div>
