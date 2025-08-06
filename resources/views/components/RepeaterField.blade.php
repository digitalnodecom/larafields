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

<div class="lftw:pr-2" data-nesting-level="{{ $nestingLevel }}">
    <!-- Collapsible Search & Export Controls -->
    <div class="lftw:mb-2">
        <!-- Toggle Button -->
        <div class="lftw:flex lftw:justify-end">
            <button
                wire:click="toggleRepeaterControls('{{ $repeaterFieldName }}')"
                class="lftw:px-2 lftw:py-1 lftw:text-xs lftw:text-gray-500 lftw:hover:text-gray-700 lftw:focus:outline-none lftw:transition-colors lftw:duration-200 lftw:flex lftw:items-center lftw:gap-1"
            >
                Search & Export
                @if($this->repeaterControlsOpen[$repeaterFieldName] ?? false)
                    ▲
                @else
                    ▼
                @endif
            </button>
        </div>

        <!-- Collapsible Content -->
        @if($this->repeaterControlsOpen[$repeaterFieldName] ?? false)
            <div class="lftw:mt-1 lftw:p-2 lftw:bg-gray-50 lftw:border lftw:border-gray-200 lftw:rounded-md">
                <div class="lftw:flex lftw:gap-2 lftw:items-end">
                    <input
                        type="text"
                        wire:model="repeaterSearch.{{ $repeaterFieldName }}"
                        placeholder="Search {{ $field['label'] }}..."
                        wire:keydown.enter.prevent="searchRepeater('{{ $repeaterFieldName }}')"
                        class="lftw:flex-grow lftw:px-2 lftw:py-1.5 lftw:border lftw:border-gray-300 lftw:rounded lftw:bg-white lftw:text-gray-900 lftw:placeholder-gray-500 lftw:focus:outline-none lftw:focus:ring-1 lftw:focus:ring-brand-500 lftw:focus:border-brand-500 lftw:text-sm"
                    >
                    <button
                        wire:click="searchRepeater('{{ $repeaterFieldName }}')"
                        class="lftw:px-3 lftw:py-1.5 lftw:bg-brand-600 lftw:text-white lftw:font-medium lftw:rounded lftw:hover:bg-brand-700 lftw:focus:outline-none lftw:focus:ring-1 lftw:focus:ring-brand-500 lftw:text-sm"
                    >
                        Search
                    </button>
                    <button
                        wire:click.prevent="exportCsv"
                        wire:loading.attr="disabled"
                        class="lftw:px-3 lftw:py-1.5 lftw:bg-green-600 lftw:text-white lftw:font-medium lftw:rounded lftw:hover:bg-green-700 lftw:focus:outline-none lftw:focus:ring-1 lftw:focus:ring-green-500 lftw:disabled:opacity-50 lftw:disabled:cursor-not-allowed lftw:text-sm"
                        title="Download CSV"
                    >
                        <span wire:loading.remove wire:target="exportCsv">CSV</span>
                        <span wire:loading wire:target="exportCsv">...</span>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Repeater table -->
    <div class="lftw:overflow-y-auto">
        <table class="lftw:min-w-full lftw:border lftw:border-gray-200 lftw:bg-white">
            <thead>
                <tr class="lftw:bg-gray-50 lftw:border-b lftw:border-gray-200">
                    @foreach($field['subfields'] as $subfield)
                        <th class="lftw:px-3 lftw:py-2 lftw:text-left lftw:text-sm lftw:font-semibold lftw:text-gray-900 lftw:border-r lftw:border-gray-200 last:lftw:border-r-0">{{ $subfield['label'] }}</th>
                    @endforeach
                    <th class="lftw:px-3 lftw:py-2 lftw:text-center lftw:text-sm lftw:font-semibold lftw:text-gray-900 lftw:w-20">Actions</th>
                </tr>
            </thead>
            <tbody class="lftw:divide-y lftw:divide-gray-200">
                @if($rowsCount === 0)
                    <tr>
                        <td colspan="{{ count($field['subfields']) + 1 }}" class="lftw:px-3 lftw:py-6 lftw:text-center lftw:text-gray-500">
                            @if(empty($this->repeaterSearch[$repeaterFieldName] ?? ''))
                                No rows available. Click "Add Row" to create one.
                            @else
                                No rows match your search query.
                            @endif
                        </td>
                    </tr>
                @else
                    @foreach($paginatedRows as $rowIndex => $repeaterItem)
                        <tr class="lftw:hover:bg-gray-50 lftw:transition-colors lftw:duration-150">
                            @foreach($field['subfields'] as $subfieldIndex => $subfield)
                                <td class="lftw:px-3 lftw:py-2 lftw:border-r lftw:border-gray-200 last:lftw:border-r-0 lftw:align-top">
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
                            <td class="lftw:px-3 lftw:py-2 lftw:text-center lftw:align-top">
                                <button
                                    wire:click.prevent="removeRepeaterRow('{{ $repeaterFieldName }}', {{ $rowIndex }})"
                                    class="lftw:px-2 lftw:py-1 lftw:bg-red-600 lftw:text-white lftw:text-xs lftw:font-medium lftw:rounded lftw:hover:bg-red-700 lftw:focus:outline-none lftw:focus:ring-1 lftw:focus:ring-red-500"
                                    title="Remove row"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        
        <!-- Add Row Button - attached to table with white background -->
        <div class="lftw:flex lftw:justify-end lftw:bg-white lftw:border lftw:border-t-0 lftw:border-gray-200 lftw:p-2">
            <button 
                wire:click.prevent="addRepeaterRow('{{ $repeaterFieldName }}')"
                class="lftw:px-3 lftw:py-1.5 lftw:bg-brand-500 lftw:text-white lftw:font-medium lftw:rounded lftw:hover:bg-brand-600 lftw:focus:outline-none lftw:focus:ring-1 lftw:focus:ring-brand-500 lftw:text-sm"
            >
                Add Row
            </button>
        </div>
    </div>

    <!-- Pagination controls -->
    @if(($this->repeaterPagination[$repeaterFieldName]['totalPages'] ?? 1) > 1)
        <div class="lftw:flex lftw:flex-col lftw:sm:flex-row lftw:sm:items-center lftw:sm:justify-between lftw:gap-2 lftw:p-2 lftw:bg-gray-50 lftw:border lftw:border-gray-200 lftw:mt-0">
            <div class="lftw:text-sm lftw:text-gray-700 lftw:text-center lftw:sm:text-left">
                Showing
                <span class="lftw:font-medium">{{ (($this->repeaterPagination[$repeaterFieldName]['currentPage'] ?? 1) - 1) * $this->itemsPerPage + 1 }}</span>
                to
                <span class="lftw:font-medium">
                    {{ min(($this->repeaterPagination[$repeaterFieldName]['currentPage'] ?? 1) * $this->itemsPerPage, $this->repeaterPagination[$repeaterFieldName]['totalItems'] ?? 0) }}
                </span>
                of
                <span class="lftw:font-medium">{{ $this->repeaterPagination[$repeaterFieldName]['totalItems'] ?? 0 }}</span>
                rows
            </div>
            <div class="lftw:flex lftw:flex-wrap lftw:justify-center lftw:sm:justify-end lftw:gap-1">
                @php
                    $currentPage = $this->repeaterPagination[$repeaterFieldName]['currentPage'] ?? 1;
                    $totalPages = $this->repeaterPagination[$repeaterFieldName]['totalPages'] ?? 1;
                @endphp
                
                <!-- First button -->
                <button
                    wire:click="changePage('{{ $repeaterFieldName }}', 1)"
                    class="lftw:px-3 lftw:py-1 lftw:text-sm lftw:border lftw:border-gray-300 lftw:bg-white lftw:text-gray-700 {{ $currentPage <= 1 ? 'lftw:opacity-50 lftw:cursor-not-allowed' : 'lftw:hover:bg-gray-50' }} lftw:transition-colors lftw:duration-200"
                    {{ $currentPage <= 1 ? 'disabled' : '' }}
                >
                    First
                </button>
                
                <!-- Previous button -->
                <button
                    wire:click="changePage('{{ $repeaterFieldName }}', {{ $currentPage - 1 }})"
                    class="lftw:px-3 lftw:py-1 lftw:text-sm lftw:border lftw:border-gray-300 lftw:bg-white lftw:text-gray-700 {{ $currentPage <= 1 ? 'lftw:opacity-50 lftw:cursor-not-allowed' : 'lftw:hover:bg-gray-50' }} lftw:transition-colors lftw:duration-200"
                    {{ $currentPage <= 1 ? 'disabled' : '' }}
                >
                    Prev
                </button>

                <!-- Page numbers -->
                @for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                    <button
                        wire:click="changePage('{{ $repeaterFieldName }}', {{ $i }})"
                        class="lftw:px-3 lftw:py-1 lftw:text-sm lftw:border {{ $i === $currentPage ? 'lftw:border-brand-500 lftw:bg-brand-500 lftw:text-white lftw:font-semibold' : 'lftw:border-gray-300 lftw:bg-white lftw:text-gray-700 lftw:hover:bg-gray-50' }} lftw:transition-colors lftw:duration-200"
                    >
                        {{ $i }}
                    </button>
                @endfor

                <!-- Next button -->
                <button
                    wire:click="changePage('{{ $repeaterFieldName }}', {{ $currentPage + 1 }})"
                    class="lftw:px-3 lftw:py-1 lftw:text-sm lftw:border lftw:border-gray-300 lftw:bg-white lftw:text-gray-700 {{ $currentPage >= $totalPages ? 'lftw:opacity-50 lftw:cursor-not-allowed' : 'lftw:hover:bg-gray-50' }} lftw:transition-colors lftw:duration-200"
                    {{ $currentPage >= $totalPages ? 'disabled' : '' }}
                >
                    Next
                </button>
                
                <!-- Last button -->
                <button
                    wire:click="changePage('{{ $repeaterFieldName }}', {{ $totalPages }})"
                    class="lftw:px-3 lftw:py-1 lftw:text-sm lftw:border lftw:border-gray-300 lftw:bg-white lftw:text-gray-700 {{ $currentPage >= $totalPages ? 'lftw:opacity-50 lftw:cursor-not-allowed' : 'lftw:hover:bg-gray-50' }} lftw:transition-colors lftw:duration-200"
                    {{ $currentPage >= $totalPages ? 'disabled' : '' }}
                >
                    Last
                </button>
            </div>
        </div>
    @endif
</div>