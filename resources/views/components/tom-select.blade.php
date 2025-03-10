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
                  if (this.tomSelect){
                    this.tomSelect.destroy();
                  }
                }
            }"
    x-init="
                if (window.Larafields && window.Larafields.tomSelect) {
                    // Use external JS functions
                    tomSelect = window.Larafields.tomSelect.initialize(
                        $refs.{{$attributes->get('key')}}, 
                        options,
                        selectValue,
                        getCleanOptions,
                        {{ $attributes->get('create') ? 'true' : 'false' }},
                        function(value) {
                            $wire.$set('{{ $attributes->whereStartsWith('wire:model')->first() }}', value, false);
                        }
                    );

                    $watch('selectValue', (newValue) => {
                        window.Larafields.tomSelect.handleSelectValueChange(tomSelect, newValue);
                    });

                    $watch('options', () => {
                        window.Larafields.tomSelect.handleOptionsChange(tomSelect, getCleanOptions);
                    });
                } else {
                    // Fallback if external JS is not loaded
                    console.error('Larafields JS not loaded. Make sure the script is properly enqueued.');
                }
              ;"
    x-ref="{{ $attributes->get('key')  }}"
    x-cloak
    {{ $attributes }}>
  </select>
</div>
