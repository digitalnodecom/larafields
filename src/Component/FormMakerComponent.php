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
        $this->groupKey = $this->generateGroupKey($group);

        $existingData = $this->fetchExistingFormData();
        $this->processFormFields($group, $existingData);
    }

    private function generateGroupKey(array $group): string
    {
        if ( $this->userContext ){
            return sprintf(
                '%s_user_%s',
                $group['name'],
                $this->userContext
            );
        }

        if ($this->taxonomyContext && $this->termOptionsContext) {
            return sprintf(
                '%s_term_option_%s_%s',
                $group['name'],
                $this->taxonomyContext,
                $this->termOptionsContext
            );
        }

        if ($this->pageContext) {
            return sprintf('%s_page_%s', $group['name'], $this->pageContext);
        }

        global $post;
        if ($post) {
            return sprintf('%s_%s', $group['name'], $post->ID);
        }

        global $pagenow;
        if ($pagenow === 'term.php') {
            return sprintf('%s_term_%s', $group['name'], $_GET['tag_ID'] ?? '');
        }

        return $group['name'];
    }

    public function submit(): void
    {
        try {
            DB::table('larafields')->updateOrInsert(
                ['form_key' => $this->groupKey],
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
