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
    public ?string $groupObjectId;
    public ?string $groupObjectType = 'user';
    public ?string $groupObjectName = '';

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

        $existingData = $this->fetchExistingFormData($group);
        $this->processFormFields($group, $existingData);
    }

    private function setGroupKeys(array $group): void
    {
        $this->groupKey = $group['name'];
        $this->groupObjectId = null;

        if ( $this->userContext ){
            $this->groupObjectType = 'user';
            $this->groupObjectName = '';
            $this->groupObjectId = $this->userContext;

            return;
        }

        if ($this->taxonomyContext && $this->termOptionsContext) {
            $this->groupObjectType = 'taxonomy';
            $this->groupObjectName = $this->taxonomyContext;
            $this->groupObjectId = $this->termOptionsContext;

            return;
        }

        if ($this->pageContext) {
            $this->groupObjectType = 'post'; // TODO: review this shit
            $this->groupObjectName = 'page';
            $this->groupObjectId = $this->pageContext;

            return;
        }

        global $post;
        if ($post) {
            $this->groupObjectType = 'post';
            $this->groupObjectName = 'post';
            $this->groupObjectId = $post->ID;

            return;
        }

        global $pagenow;
        if ($pagenow === 'term.php') {
            $this->groupObjectType = 'taxonomy';
            $this->groupObjectName = $_GET['taxonomy'];
            $this->groupObjectId = $_GET['tag_ID'];
        }
    }

    public function submit(): void
    {
        try {
        collect($this->availablePropertiesData)
            ->each(function($field, $key){
                DB::table('larafields')->updateOrInsert(
                    [
                        'object_type' => $this->groupObjectType,
                        'object_name' => $this->groupObjectName,
                        'object_id' => $this->groupObjectId,
                        'field_key' => $key
                    ],
                    ['field_value' => json_encode($field)]
                );

                session()->flash('message', 'Form saved successfully.');
            });
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
