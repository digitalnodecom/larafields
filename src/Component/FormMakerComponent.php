<?php

namespace DigitalNode\FormMaker\Component;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FormMakerComponent extends Component {
    public array $availablePropertiesSchema = [];
    public array $availablePropertiesData = [];

    public string $groupKey;
    private string $is_on_page = '';

    public function mount( $group, $is_on_page = false ) {
        $this->is_on_page = $is_on_page;
        $this->groupKey = $this->getGroupKey($group);

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
            } else if ( $field['type'] == 'repeater' ){
                $this->availablePropertiesSchema[] = [
                    'type'      => 'repeater',
                    'name'      => $field['name'],
                    'label'     => $field['label'],
                    'subfields' => $field['subfields'],
                ];

                $defaultValue = $existingData['dn_form_maker_' . $field['name']] ?? $field['defaultValue'] ?? [];

                $this->availablePropertiesData[ 'dn_form_maker_' . $field['name'] ] = $defaultValue;
            }
        } );
    }

    public function addRepeaterRow($fieldName) {
        $this->availablePropertiesData['dn_form_maker_' . $fieldName][] = [];
    }

    public function removeRepeaterRow($fieldName, $index) {
        unset($this->availablePropertiesData['dn_form_maker_' . $fieldName][$index]);
        $this->availablePropertiesData['dn_form_maker_' . $fieldName] = array_values($this->availablePropertiesData['dn_form_maker_' . $fieldName]);
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

    private function getGroupKey( $group ) {
        if ( $this->is_on_page ){
            return sprintf("%s_%s", $group['name'], $this->is_on_page);
        }

        global $post;

        if ( $post ){
            return sprintf("%s_%s", $group['name'], $post->ID);
        }

        global $pagenow;

        if ( $pagenow == 'term.php' ){
            return sprintf("%s_%s", $group['name'], $_GET['tag_ID']);
        }

        return $group['label'];
    }
}
