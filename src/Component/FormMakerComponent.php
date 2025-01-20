<?php

namespace DigitalNode\FormMaker\Component;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FormMakerComponent extends Component {
    public array $availablePropertiesSchema = [];
    public array $availablePropertiesData = [];

    public string $groupKey;

    public function mount( $group ) {
        $this->groupKey = $group['name'];

        $existingData = DB::table('form_submissions')
          ->where('form_key', $this->groupKey)
          ->first();

        if ( $existingData ){
            $existingData = json_decode($existingData->form_content, true);
        }

        collect( $group['fields'] )->each( function ( $field ) use ($existingData) {
            $defaultValue = $existingData['dn_form_maker_' . $field['name']] ?? $field['defaultValue'] ?? '';

            $this->availablePropertiesData[ 'dn_form_maker_' . $field['name'] ] = $defaultValue;

            if ( collect( [ 'text', 'textarea', 'number' ] )->contains( $field['type'] ) ) {
                $this->availablePropertiesSchema[] = [
                    'type'     => $field['type'],
                    'name'     => $field['name'],
                    'label'    => $field['label'],
                    'required' => $field['required']
                ];
            } else if ( $field['type'] == 'multiselect' ) {
                $this->availablePropertiesSchema[] = [
                    'type'     => $field['type'],
                    'name'     => $field['name'],
                    'label'    => $field['label'],
                    'required' => $field['required'],
                    'options'  => $field['options']
                ];
            }
        } );
    }

    public function submit() {
        try {
            DB::table( 'form_submissions' )
              ->updateOrInsert( [
                  'form_key' => $this->groupKey,
              ], [
                  'form_content' => json_encode( $this->availablePropertiesData )
              ] );

            session()->flash( 'message', 'Form has been saved successfully.' );
        } catch ( \Exception $exception ) {
            session()->flash( 'message', 'There has been an error with the form submission. Error was: ' . $exception->getMessage() );
        }
    }

    public function render() {
        return view( 'FormMaker::livewire.form-maker' )
            ->layout( 'FormMaker::livewire.layout' );
    }
}
