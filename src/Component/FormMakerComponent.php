<?php

namespace DigitalNode\FormMaker\Component;

use Livewire\Component;

class FormMakerComponent extends Component
{
    public array $availablePropertiesSchema = [];
    public array $availablePropertiesData = [];

    public string $groupKey;

    public function mount($group) {
        $this->groupKey = $group['name'];

        collect($group['fields'])->each(function($field){
            if ( collect(['text', 'textarea', 'number'])->contains($field['type']) ){
                $this->availablePropertiesData['dn_form_maker_' . $field['name']] = $field['defaultValue'];

                $this->availablePropertiesSchema[] = [
                    'type' => $field['type'],
                    'name' => $field['name'],
                    'label' => $field['label'],
                    'required' => $field['required']
                ];
            }
        });

    }

    public function submit(){
        dd($this->groupKey);
    }

    public function render()
    {
        return view('FormMaker::livewire.form-maker')
            ->layout('FormMaker::livewire.layout');
    }
}
