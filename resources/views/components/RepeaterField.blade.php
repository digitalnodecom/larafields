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

<div class="lftw:bg-white lftw:border lftw:border-gray-200 lftw:p-6" data-nesting-level="{{ $nestingLevel }}">
    <!-- Search input - show for top-level repeaters or when showSearch is enabled -->
    @if($nestingLevel === 1 || ($field['showSearch'] ?? false))
        <div class="lftw:mb-6 lftw:flex lftw:flex-col lftw:sm:flex-row lftw:gap-3">
            <div class="lftw:relative lftw:flex-grow">
                <input
                    type="text"
                    wire:model="repeaterSearch.{{ $repeaterFieldName }}"
                    placeholder="Search rows..."
                    wire:keydown.enter.prevent="searchRepeater('{{ $repeaterFieldName }}')"
                    class="lftw:w-full lftw:pl-10 lftw:pr-4 lftw:py-2 lftw:border lftw:border-gray-300 lftw:bg-white lftw:text-gray-900 lftw:placeholder-gray-500 lftw:focus:outline-none lftw:focus:ring-2 lftw:focus:ring-brand-500 lftw:focus:border-brand-500 lftw:transition-colors lftw:duration-200"
                >
                <div class="lftw:absolute lftw:inset-y-0 lftw:left-0 lftw:pl-3 lftw:flex lftw:items-center lftw:pointer-events-none">
                    <svg class="lftw:h-4 lftw:w-4 lftw:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <button
                wire:click="searchRepeater('{{ $repeaterFieldName }}')"
                class="lftw:px-4 lftw:py-2 lftw:bg-gray-600 lftw:text-white lftw:font-medium lftw:hover:bg-gray-700 lftw:focus:outline-none lftw:focus:ring-2 lftw:focus:ring-gray-500 lftw:focus:ring-offset-2 lftw:transition-colors lftw:duration-200 lftw:whitespace-nowrap"
            >
                Search
            </button>
        </div>
    @endif

    <!-- Repeater table -->
    <div class="lftw:overflow-x-auto lftw:mb-6">
        <table class="lftw:min-w-full lftw:border lftw:border-gray-200 lftw:bg-white">
            <thead>
                <tr class="lftw:bg-gray-50 lftw:border-b lftw:border-gray-200">
                    @foreach($field['subfields'] as $subfield)
                        <th class="lftw:px-4 lftw:py-3 lftw:text-left lftw:text-sm lftw:font-semibold lftw:text-gray-900 lftw:border-r lftw:border-gray-200 last:lftw:border-r-0">{{ $subfield['label'] }}</th>
                    @endforeach
                    <th class="lftw:px-4 lftw:py-3 lftw:text-center lftw:text-sm lftw:font-semibold lftw:text-gray-900 lftw:w-20">Actions</th>
                </tr>
            </thead>
            <tbody class="lftw:divide-y lftw:divide-gray-200">
                @if($rowsCount === 0)
                    <tr>
                        <td colspan="{{ count($field['subfields']) + 1 }}" class="lftw:px-4 lftw:py-8 lftw:text-center lftw:text-gray-500">
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
                                <td class="lftw:px-4 lftw:py-3 lftw:border-r lftw:border-gray-200 last:lftw:border-r-0 lftw:align-top">
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
                            <td class="lftw:px-4 lftw:py-3 lftw:text-center lftw:align-top">
                                <button
                                    wire:click.prevent="removeRepeaterRow('{{ $repeaterFieldName }}', {{ $rowIndex }})"
                                    class="lftw:inline-flex lftw:items-center lftw:px-2 lftw:py-1 lftw:bg-danger-600 lftw:text-white lftw:text-sm lftw:font-medium lftw:hover:bg-danger-700 lftw:focus:outline-none lftw:focus:ring-2 lftw:focus:ring-danger-500 lftw:focus:ring-offset-2 lftw:transition-colors lftw:duration-200"
                                    title="Remove row"
                                >
                                    <svg class="lftw:w-3 lftw:h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
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
        <div class="lftw:flex lftw:flex-col lftw:sm:flex-row lftw:sm:items-center lftw:sm:justify-between lftw:mb-6 lftw:gap-3 lftw:p-3 lftw:bg-gray-50 lftw:border lftw:border-gray-200">
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

    <!-- Add Row Button -->
    <button 
        wire:click.prevent="addRepeaterRow('{{ $repeaterFieldName }}')"
        class="lftw:inline-flex lftw:items-center lftw:gap-2 lftw:px-4 lftw:py-2 lftw:bg-brand-500 lftw:text-white lftw:font-medium lftw:hover:bg-brand-600 lftw:focus:outline-none lftw:focus:ring-2 lftw:focus:ring-brand-500 lftw:focus:ring-offset-2 lftw:transition-colors lftw:duration-200"
    >
        <svg class="lftw:w-4 lftw:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Add Row
    </button>
</div>