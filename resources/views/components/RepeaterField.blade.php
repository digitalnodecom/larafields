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
        <div class="mb-4 flex flex-col sm:flex-row gap-2">
            <div class="relative flex-grow">
                <input
                    type="text"
                    wire:model="repeaterSearch.{{ $repeaterFieldName }}"
                    placeholder="Search rows..."
                    class="w-full p-2 border border-gray-300 rounded !pl-10"
                    wire:keydown.enter.prevent="searchRepeater('{{ $repeaterFieldName }}')"
                >
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <button
                wire:click="searchRepeater('{{ $repeaterFieldName }}')"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 w-full sm:w-auto"
            >
                Search
            </button>
        </div>
    @endif

    <!-- Repeater table -->
    <div class="overflow-x-auto">
        <table class="border-collapse border border-gray-300 mb-4">
            <thead>
                <tr>
                    @foreach($field['subfields'] as $subfield)
                        <th class="border border-gray-300 p-2 bg-gray-100">{{ $subfield['label'] }}</th>
                    @endforeach
                    <th class="border border-gray-300 p-2 bg-gray-100">Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($rowsCount === 0)
                    <tr>
                        <td colspan="{{ count($field['subfields']) + 1 }}" class="border border-gray-300 p-4 text-center text-gray-500">
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
                                <td class="border border-gray-300 p-2">
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
                            <td class="border border-gray-300 p-2 text-center">
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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-3">
            <div class="text-sm text-gray-700 text-center sm:text-left">
                Showing
                <span class="font-medium">{{ (($this->repeaterPagination[$repeaterFieldName]['currentPage'] ?? 1) - 1) * $this->itemsPerPage + 1 }}</span>
                to
                <span class="font-medium">
                    {{ min(($this->repeaterPagination[$repeaterFieldName]['currentPage'] ?? 1) * $this->itemsPerPage, $this->repeaterPagination[$repeaterFieldName]['totalItems'] ?? 0) }}
                </span>
                of
                <span class="font-medium">{{ $this->repeaterPagination[$repeaterFieldName]['totalItems'] ?? 0 }}</span>
                rows
            </div>
            <div class="flex flex-wrap justify-center sm:justify-end gap-1 sm:gap-2">
                @php
                    $currentPage = $this->repeaterPagination[$repeaterFieldName]['currentPage'] ?? 1;
                    $totalPages = $this->repeaterPagination[$repeaterFieldName]['totalPages'] ?? 1;
                @endphp
                
                <button
                    wire:click="changePage('{{ $repeaterFieldName }}', {{ $currentPage - 1 }})"
                    class="px-2 sm:px-3 py-1 text-xs sm:text-sm rounded border border-gray-300 {{ $currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100' }}"
                    {{ $currentPage <= 1 ? 'disabled' : '' }}
                >
                    Prev
                </button>

                @for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                    <button
                        wire:click="changePage('{{ $repeaterFieldName }}', {{ $i }})"
                        class="px-2 sm:px-3 py-1 text-xs sm:text-sm rounded border {{ $i === $currentPage ? 'border-blue-600 bg-blue-600 text-white font-semibold shadow-md' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400' }}"
                        style="{{ $i === $currentPage ? 'background:rgb(19, 63, 158) !important; color: white !important; border-color: #2563eb !important;' : '' }}"
                    >
                        {{ $i }}
                    </button>
                @endfor

                <button
                    wire:click="changePage('{{ $repeaterFieldName }}', {{ $currentPage + 1 }})"
                    class="px-2 sm:px-3 py-1 text-xs sm:text-sm rounded border border-gray-300 {{ $currentPage >= $totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100' }}"
                    {{ $currentPage >= $totalPages ? 'disabled' : '' }}
                >
                    Next
                </button>
            </div>
        </div>
    @endif

    <button 
        wire:click.prevent="addRepeaterRow('{{ $repeaterFieldName }}')"
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
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
