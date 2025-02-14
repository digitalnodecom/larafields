<?php

namespace DigitalNode\FormMaker\Component;

use DigitalNode\FormMaker\Component\Traits\HasRepeaterFields;
use DigitalNode\FormMaker\Component\Traits\HasProcessesFields;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FormMakerComponent extends Component
{
    use HasProcessesFields, HasRepeaterFields;

    public array $availablePropertiesSchema = [];

    public array $availablePropertiesData = [];

    public string $groupKey;

    private string $is_on_page = '';

    private string $is_on_term_options_page = '';

    private string $taxonomy = '';

    public function mount($group, $is_on_page = '', $is_on_term_options_page = '', $taxonomy = '')
    {
        $this->initializeProperties($is_on_page, $is_on_term_options_page, $taxonomy);
        $this->groupKey = $this->getGroupKey($group);
        $existingData = $this->getExistingFormData();
        $this->processFields($group, $existingData);
    }

    public function submit()
    {
        try {
            DB::table('form_submissions')
              ->updateOrInsert([
                  'form_key' => $this->groupKey,
              ], [
                  'form_content' => json_encode($this->availablePropertiesData),
              ]);

            session()->flash('message', 'Form has been saved successfully.');
        } catch (\Exception $exception) {
            session()->flash('message', 'There has been an error with the form submission. Error was: '.$exception->getMessage());
        }
    }

    public function render()
    {
        return view('FormMaker::livewire.form-maker')
            ->layout('FormMaker::livewire.layout');
    }


    private function getGroupKey($group)
    {
        if ($this->taxonomy && $this->is_on_term_options_page) {
            return sprintf('%s_term_option_%s_%s', $group['name'], $this->taxonomy, $this->is_on_term_options_page);
        }

        if ($this->is_on_page) {
            return sprintf('%s_page_%s', $group['name'], $this->is_on_page);
        }

        global $post;

        if ($post) {
            return sprintf('%s_%s', $group['name'], $post->ID);
        }

        global $pagenow;

        if ($pagenow == 'term.php') {
            return sprintf('%s_term_%s', $group['name'], $_GET['tag_ID']);
        }

        return $group['label'];
    }
}
