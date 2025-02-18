<?php

namespace DigitalNode\Larafields\Component;

use DigitalNode\Larafields\Component\Traits\HasProcessesFields;
use DigitalNode\Larafields\Component\Traits\HasRepeaterFields;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class FormMakerComponent extends Component
{
    use HasProcessesFields, HasRepeaterFields;

    public array $availablePropertiesSchema = [];
    public array $availablePropertiesData = [];
    public string $groupKey;
    public string $groupLocationMeta = '';

    private ?string $pageContext = null;
    private ?string $termOptionsContext = null;
    private ?string $taxonomyContext = null;
    private ?string $userContext = null;

    public function mount(
        array $group,
        ?string $pageContext = null,
        ?string $termOptionsContext = null,
        ?string $taxonomyContext = null,
        ?string $userContext = null)
    : void {
        $this->initializeContextProperties($pageContext, $termOptionsContext, $taxonomyContext, $userContext);
        $this->setGroupKeys($group);

        $existingData = $this->fetchExistingFormData();
        $this->processFormFields($group, $existingData);
    }

    private function setGroupKeys(array $group): void
    {
        $this->groupKey = $group['name'];

        if ( $this->userContext ){
            $this->groupLocationMeta = sprintf(
                'user_%s',
                $this->userContext
            );

            return;
        }

        if ($this->taxonomyContext && $this->termOptionsContext) {
            $this->groupLocationMeta = sprintf(
                'term_option_%s_%s',
                $this->taxonomyContext,
                $this->termOptionsContext
            );

            return;
        }

        if ($this->pageContext) {
            $this->groupLocationMeta = sprintf(
                'page_%s',
                $this->pageContext
            );

            return;
        }

        global $post;
        if ($post) {
            $this->groupLocationMeta = sprintf(
                'post_%s',
                $post->ID,
            );

            return;
        }

        global $pagenow;
        if ($pagenow === 'term.php') {
            $this->groupLocationMeta = sprintf(
                'term_%s',
                $_GET['tag_ID']
            );
        }
    }

    public function submit(): void
    {
        try {
            DB::table('larafields')->updateOrInsert(
                ['form_key' => $this->groupKey, 'form_location_meta' => $this->groupLocationMeta],
                ['form_content' => json_encode($this->availablePropertiesData)]
            );

            session()->flash('message', 'Form saved successfully.');
        } catch (\Exception $exception) {
            Log::error('Form submission error: ' . $exception->getMessage());
            session()->flash('message', 'An error occurred while saving the form.');
        }
    }

    public function render()
    {
        return view('Larafields::livewire.form-maker')
            ->layout('Larafields::livewire.layout');
    }
}
