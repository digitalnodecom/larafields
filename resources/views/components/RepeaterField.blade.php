@props(['field', 'fieldKey', 'parentFieldName' => null, 'nestingLevel' => 1, 'schemaKey' => null, 'subfieldIndex' => null])

@php
    // Generate unique identifiers for nested repeaters
    $repeaterFieldName = $parentFieldName ? 
        "{$parentFieldName}.{$field['name']}" : 
        $field['name'];
    
    // Initialize repeater state if not exists
    if (!isset($this->repeaterPagination[$repeaterFieldName])) {
        $this->initRepeaterPagination($repeaterFieldName);
    }
    
    $repeaterData = data_get($this->availablePropertiesData, $repeaterFieldName, []);
    $paginatedRows = $this->getPaginatedRepeaterRows($repeaterFieldName);
    $rowsCount = count($paginatedRows);
@endphp

<div class="repeater" data-nesting-level="{{ $nestingLevel }}">
    <!-- Search input with button - show for top-level repeaters or when showSearch is enabled -->
    @if($nestingLevel === 1 || ($field['showSearch'] ?? false))
        <div class="lftw:mb-4 lftw:flex lftw:flex-col lftw:sm:flex-row lftw:gap-2">
            <div class="lftw:relative lftw:flex-grow">
                <input
                    type="text"
                    wire:model="repeaterSearch.{{ $repeaterFieldName }}"
                    placeholder="Search rows..."
                    wire:keydown.enter.prevent="searchRepeater('{{ $repeaterFieldName }}')"
                    class="lftw:w-full lftw:pl-10 lftw:pr-4 lftw:py-2 lftw:border lftw:border-gray-300 lftw:rounded-lg lftw:focus:ring-2 lftw:focus:ring-blue-500 lftw:focus:border-blue-500 lftw:outline-none lftw:transition-colors lftw:duration-200"
                >
                <div class="lftw:absolute lftw:inset-y-0 lftw:left-0 lftw:pl-3 lftw:flex lftw:items-center lftw:pointer-events-none">
                    <svg class="lftw:h-5 lftw:w-5 lftw:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <button
                wire:click="searchRepeater('{{ $repeaterFieldName }}')"
                class="lftw:px-4 lftw:py-2 lftw:bg-blue-600 lftw:text-white lftw:rounded-lg lftw:hover:bg-blue-700 lftw:focus:ring-2 lftw:focus:ring-blue-500 lftw:focus:ring-offset-2 lftw:outline-none lftw:transition-colors lftw:duration-200 lftw:font-medium lftw:whitespace-nowrap"
            >
                Search
            </button>
        </div>
    @endif

    <!-- Repeater table -->
    <div class="lftw-overflow-x-auto">
        <table class="lftw-border-collapse lftw-border lftw-border-gray-300 lftw-mb-4">
            <thead>
                <tr>
                    @foreach($field['subfields'] as $subfield)
                        <th class="lftw-border lftw-border-gray-300 lftw-p-2 lftw-bg-gray-100">{{ $subfield['label'] }}</th>
                    @endforeach
                    <th class="lftw-border lftw-border-gray-300 lftw-p-2 lftw-bg-gray-100">Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($rowsCount === 0)
                    <tr>
                        <td colspan="{{ count($field['subfields']) + 1 }}" class="lftw-border lftw-border-gray-300 lftw-p-4 lftw-text-center lftw-text-gray-500">
                            @if(empty($this->repeaterSearch[$repeaterFieldName] ?? ''))
                                No rows available. Click "Add Row" to create one.
                            @else
                                No rows match your search query.
                            @endif
                        </td>
                    </tr>
                @else
                    @foreach($paginatedRows as $rowIndex => $repeaterItem)
                        <tr class="repeater-row">
                            @foreach($field['subfields'] as $subfieldIndex => $subfield)
                                <td class="lftw-border lftw-border-gray-300 lftw-p-2">
                                    @include('Larafields::components.RecursiveField', [
                                        'field' => $subfield,
                                        'fieldKey' => "{$fieldKey}.{$rowIndex}.{$subfield['name']}",
                                        'index' => $rowIndex,
                                        'parentFieldName' => $repeaterFieldName,
                                        'nestingLevel' => $nestingLevel,
                                        'schemaKey' => $schemaKey ?? 0,
                                        'subfieldIndex' => $subfieldIndex
                                    ])
                                </td>
                            @endforeach
                            <td class="lftw-border lftw-border-gray-300 lftw-p-2 lftw-text-center">
                                <button
                                    wire:click.prevent="removeRepeaterRow('{{ $repeaterFieldName }}', {{ $rowIndex }})"
                                >
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Pagination controls -->
    @if(($this->repeaterPagination[$repeaterFieldName]['totalPages'] ?? 1) > 1)
        <div class="lftw-flex lftw-flex-col lftw-sm:flex-row lftw-sm:items-center lftw-sm:justify-between lftw-mb-4 lftw-gap-3">
            <div class="lftw-text-sm lftw-text-gray-700 lftw-text-center lftw-sm:text-left">
                Showing
                <span class="lftw-font-medium">{{ (($this->repeaterPagination[$repeaterFieldName]['currentPage'] ?? 1) - 1) * $this->itemsPerPage + 1 }}</span>
                to
                <span class="lftw-font-medium">
                    {{ min(($this->repeaterPagination[$repeaterFieldName]['currentPage'] ?? 1) * $this->itemsPerPage, $this->repeaterPagination[$repeaterFieldName]['totalItems'] ?? 0) }}
                </span>
                of
                <span class="lftw-font-medium">{{ $this->repeaterPagination[$repeaterFieldName]['totalItems'] ?? 0 }}</span>
                rows
            </div>
            <div class="lftw-flex lftw-flex-wrap lftw-justify-center lftw-sm:justify-end lftw-gap-1 lftw-sm:gap-2">
                @php
                    $currentPage = $this->repeaterPagination[$repeaterFieldName]['currentPage'] ?? 1;
                    $totalPages = $this->repeaterPagination[$repeaterFieldName]['totalPages'] ?? 1;
                @endphp
                
                <!-- First button -->
                <button
                    wire:click="changePage('{{ $repeaterFieldName }}', 1)"
                    class="lftw-px-2 lftw-sm:px-3 lftw-py-1 lftw-text-xs lftw-sm:text-sm lftw-rounded lftw-border lftw-border-gray-300 {{ $currentPage <= 1 ? 'lftw-opacity-50 lftw-cursor-not-allowed' : 'lftw-hover:bg-gray-100' }}"
                    {{ $currentPage <= 1 ? 'disabled' : '' }}
                >
                    First
                </button>
                
                <!-- Previous button -->
                <button
                    wire:click="changePage('{{ $repeaterFieldName }}', {{ $currentPage - 1 }})"
                    class="lftw-px-2 lftw-sm:px-3 lftw-py-1 lftw-text-xs lftw-sm:text-sm lftw-rounded lftw-border lftw-border-gray-300 {{ $currentPage <= 1 ? 'lftw-opacity-50 lftw-cursor-not-allowed' : 'lftw-hover:bg-gray-100' }}"
                    {{ $currentPage <= 1 ? 'disabled' : '' }}
                >
                    Prev
                </button>

                <!-- Page numbers -->
                @for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                    <button
                        wire:click="changePage('{{ $repeaterFieldName }}', {{ $i }})"
                        class="lftw-px-2 lftw-sm:px-3 lftw-py-1 lftw-text-xs lftw-sm:text-sm lftw-rounded lftw-border {{ $i === $currentPage ? 'lftw-border-blue-600 lftw-bg-blue-600 lftw-text-white lftw-font-semibold lftw-shadow-md' : 'lftw-border-gray-300 lftw-bg-white lftw-text-gray-700 lftw-hover:bg-gray-50 lftw-hover:border-gray-400' }}"
                        style="{{ $i === $currentPage ? 'background:rgb(7, 36, 98) !important; color: white !important; border-color: #2563eb !important;' : '' }}"
                    >
                        {{ $i }}
                    </button>
                @endfor

                <!-- Next button -->
                <button
                    wire:click="changePage('{{ $repeaterFieldName }}', {{ $currentPage + 1 }})"
                    class="lftw-px-2 lftw-sm:px-3 lftw-py-1 lftw-text-xs lftw-sm:text-sm lftw-rounded lftw-border lftw-border-gray-300 {{ $currentPage >= $totalPages ? 'lftw-opacity-50 lftw-cursor-not-allowed' : 'lftw-hover:bg-gray-100' }}"
                    {{ $currentPage >= $totalPages ? 'disabled' : '' }}
                >
                    Next
                </button>
                
                <!-- Last button -->
                <button
                    wire:click="changePage('{{ $repeaterFieldName }}', {{ $totalPages }})"
                    class="lftw-px-2 lftw-sm:px-3 lftw-py-1 lftw-text-xs lftw-sm:text-sm lftw-rounded lftw-border lftw-border-gray-300 {{ $currentPage >= $totalPages ? 'lftw-opacity-50 lftw-cursor-not-allowed' : 'lftw-hover:bg-gray-100' }}"
                    {{ $currentPage >= $totalPages ? 'disabled' : '' }}
                >
                    Last
                </button>
            </div>
        </div>
    @endif

    <button 
        wire:click.prevent="addRepeaterRow('{{ $repeaterFieldName }}')"
        class="lftw-bg-blue-500 lftw-text-white lftw-px-4 lftw-py-2 lftw-rounded lftw-hover:bg-blue-600"
    >
        Add Row
    </button>
</div>

<style>
    .repeater[data-nesting-level="1"] {
        padding-left: 0.2rem;
    }
    .repeater[data-nesting-level="2"] {
        padding-left: 0.2rem;
    }
    .repeater[data-nesting-level="3"] {
        padding-left: 0.2rem;
    }
    .repeater[data-nesting-level="4"] {
        padding-left: 0.2rem;
    }
</style>
