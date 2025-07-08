<?php

namespace DigitalNode\Larafields\Component;

use DigitalNode\Larafields\Component\Traits\HasProcessesFields;
use DigitalNode\Larafields\Component\Traits\HasRepeaterFields;
use DigitalNode\Larafields\Component\Traits\HasValidation;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class FormMakerComponent extends Component
{
    use HasProcessesFields, HasRepeaterFields, WithFileUploads, HasValidation;

    public array $availablePropertiesSchema = [];

    public array $availablePropertiesData = [];

    public ?string $groupObjectId;

    public ?string $groupObjectType = 'user';

    public ?string $groupObjectName = '';

    // Pagination and search properties
    public array $repeaterPagination = [];

    public array $repeaterSearch = [];

    public int $itemsPerPage = 25;

    private ?string $pageContext = null;

    private ?string $termOptionsContext = null;

    private ?string $taxonomyContext = null;

    private ?string $userContext = null;

    public function mount(
        array $group,
        ?string $pageContext = null,
        ?string $termOptionsContext = null,
        ?string $taxonomyContext = null,
        ?string $userContext = null): void
    {
        $this->initializeContextProperties($pageContext, $termOptionsContext, $taxonomyContext, $userContext);
        $this->setGroupKeys($group);

        $existingData = $this->fetchExistingFormData($group);
        $this->processFormFields($group, $existingData);

        // Initialize pagination for repeater fields
        foreach ($this->availablePropertiesSchema as $key => $field) {
            if ($field['type'] === 'repeater') {
                $this->initRepeaterPagination($field['name']);
            }
        }
    }

    private function setGroupKeys(array $group): void
    {
        $this->groupObjectId = null;

        if ($this->userContext) {
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
            $this->groupObjectType = 'post_type'; // TODO: review this shit
            $this->groupObjectName = 'page';
            $this->groupObjectId = $this->pageContext;

            return;
        }

        global $post;
        if ($post) {
            $this->groupObjectType = 'post_type';
            $this->groupObjectName = $post->post_type;
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
        // Clear previous validation errors
        $this->clearValidationErrors();

        // Validate the form before submission
        if (!$this->validateForm()) {
            session()->flash('message', 'Please fix the validation errors before submitting.');
            return;
        }

        try {
            DB::beginTransaction();
            collect($this->availablePropertiesData)
                ->each(function ($field, $key) {
                    if (is_array($field)) {
                        $field = collect($field)->map(function ($repeaterField) {
                            return $this->processFieldBeforeStoring($repeaterField);
                        })->toArray();
                    }

                    DB::table('larafields')->updateOrInsert(
                        [
                            'object_type' => $this->groupObjectType,
                            'object_name' => $this->groupObjectName,
                            'object_id' => $this->groupObjectId,
                            'field_key' => $key,
                        ],
                        ['field_value' => json_encode($field)]
                    );

                    session()->flash('message', 'Form saved successfully.');
                });
            DB::commit();
        } catch (\Exception) {
            session()->flash('message', 'An error occurred while saving the form.');

            DB::rollBack();
        }
    }

    public function render()
    {
        // Process repeater fields for pagination and search
        foreach ($this->availablePropertiesSchema as $key => $field) {
            if ($field['type'] === 'repeater' && isset($this->availablePropertiesData[$field['name']])) {
                $this->updateRepeaterPagination($field['name']);
            }
        }

        return view('Larafields::livewire.form-maker')
            ->layout('Larafields::livewire.layout');
    }

    /**
     * Initialize pagination for a repeater field
     */
    public function initRepeaterPagination(string $fieldName): void
    {
        $this->repeaterPagination[$fieldName] = [
            'currentPage' => 1,
            'totalPages' => 1,
            'totalItems' => 0,
        ];

        $this->repeaterSearch[$fieldName] = '';

        $this->updateRepeaterPagination($fieldName);
    }

    /**
     * Update pagination information for a repeater field
     */
    public function updateRepeaterPagination(string $fieldName): void
    {
        if (! isset($this->availablePropertiesData[$fieldName])) {
            return;
        }

        $filteredRows = $this->getFilteredRepeaterRows($fieldName);
        $totalItems = count($filteredRows);
        $totalPages = max(1, ceil($totalItems / $this->itemsPerPage));

        $this->repeaterPagination[$fieldName]['totalItems'] = $totalItems;
        $this->repeaterPagination[$fieldName]['totalPages'] = $totalPages;

        // Ensure current page is valid
        if ($this->repeaterPagination[$fieldName]['currentPage'] > $totalPages) {
            $this->repeaterPagination[$fieldName]['currentPage'] = $totalPages;
        }
    }

    /**
     * Get filtered repeater rows based on search query
     */
    public function getFilteredRepeaterRows(string $fieldName): array
    {
        if (! isset($this->availablePropertiesData[$fieldName])) {
            return [];
        }

        $rows = $this->availablePropertiesData[$fieldName];
        $searchQuery = $this->repeaterSearch[$fieldName] ?? '';

        if (empty($searchQuery)) {
            return $rows;
        }

        // Filter rows based on search query
        return collect($rows)->filter(function ($row) use ($searchQuery) {
            // Search in all subfields of the row
            foreach ($row as $value) {
                // Convert value to string and check if it contains the search query
                $stringValue = is_array($value) ? json_encode($value) : (string) $value;
                if (stripos($stringValue, $searchQuery) !== false) {
                    return true;
                }
            }

            return false;
        })->toArray();
    }

    /**
     * Get paginated repeater rows
     */
    public function getPaginatedRepeaterRows(string $fieldName): array
    {
        $filteredRows = $this->getFilteredRepeaterRows($fieldName);

        if (empty($filteredRows)) {
            return [];
        }

        $currentPage = $this->repeaterPagination[$fieldName]['currentPage'];
        $offset = ($currentPage - 1) * $this->itemsPerPage;

        return array_slice($filteredRows, $offset, $this->itemsPerPage, true);
    }

    /**
     * Change the current page for a repeater field
     */
    public function changePage(string $fieldName, int $page): void
    {
        if (! isset($this->repeaterPagination[$fieldName])) {
            return;
        }

        $totalPages = $this->repeaterPagination[$fieldName]['totalPages'];

        // Ensure page is within valid range
        $page = max(1, min($page, $totalPages));

        $this->repeaterPagination[$fieldName]['currentPage'] = $page;
    }

    /**
     * Update search query for a repeater field
     */
    public function searchRepeater(string $fieldName): void
    {
        // The query is already stored in $this->repeaterSearch[$fieldName] via wire:model

        // Reset to first page when search query changes
        $this->repeaterPagination[$fieldName]['currentPage'] = 1;

        $this->updateRepeaterPagination($fieldName);
    }

    private function processFieldBeforeStoring($repeaterField)
    {
        if (is_array($repeaterField)) {
            return collect($repeaterField)->map(function ($subRepeaterField) {
                if (is_array($subRepeaterField)) {
                    return $this->processFieldBeforeStoring($subRepeaterField);
                }

                if (is_object($subRepeaterField) && get_class($subRepeaterField) == TemporaryUploadedFile::class) {
                    $file = $subRepeaterField->storePublicly('larafields');

                    return $file;
                }

                if (json_validate($subRepeaterField)) {
                    return json_decode($subRepeaterField, true);
                }

                return $subRepeaterField;
            });
        } elseif (json_validate($repeaterField)) {
            return json_decode($repeaterField, true);
        }

        return $repeaterField;
    }
}
