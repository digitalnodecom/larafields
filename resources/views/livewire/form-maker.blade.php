@php use Illuminate\Support\Facades\Session; @endphp

<div class="space-y-8">
  @foreach($availablePropertiesSchema as $key => $field)
    <div class="flex flex-col">
      <label class="text-xl font-semibold mb-3">{{ $field['label'] }}</label>

      @php
        $field['key'] = 'availablePropertiesData.' . $field['name'];
      @endphp

      @if($field['type'] == 'text')
        @include('Larafields::components.TextField', ['field' => $field])
      @endif

      @if($field['type'] == 'number')
        @include('Larafields::components.NumberField', ['field' => $field])
      @endif

      @if($field['type'] == 'textarea')
        @include('Larafields::components.TextareaField', ['field' => $field])
      @endif

      @if($field['type'] == 'datetime')
        @include('Larafields::components.DateTimeField', ['field' => $field])
      @endif

      @if($field['type'] == 'date')
        @include('Larafields::components.DateField', ['field' => $field])
      @endif

      @if($field['type'] == 'week')
        @include('Larafields::components.WeekField', ['field' => $field])
      @endif

      @if($field['type'] == 'month')
        @include('Larafields::components.MonthField', ['field' => $field])
      @endif

      @if($field['type'] == 'file')
        @include('Larafields::components.FileField', ['field' => $field])
      @endif

      @if($field['type'] == 'multiselect')
        <x-tom-select
          class="multiselect"
          wire:model="{!! sprintf('availablePropertiesData.%s', $field['name']) !!}"
          options="{!! sprintf('availablePropertiesSchema.%s.options', $key) !!}"
          key="ms{{$key}}"
          :create="($field['custom_values'] ?? false) ? true : null"
          multiple
        />
      @endif

      @if($field['type'] == 'select')
        <x-tom-select
          class="multiselect"
          wire:model="{!! sprintf('availablePropertiesData.%s', $field['name']) !!}"
          options="{!! sprintf('availablePropertiesSchema.%s.options', $key) !!}"
          key="ms{{$key}}"
          :create="($field['custom_values'] ?? false) ? true : null"
        />
      @endif

      @if($field['type'] == 'repeater')
        <div class="repeater">
          <!-- Search input with button -->
          <div class="mb-4 flex">
            <div class="relative flex-grow">
              <input
                type="text"
                wire:model="repeaterSearch.{{ $field['name'] }}"
                placeholder="Search rows..."
                class="w-full p-2 border border-gray-300 rounded !pl-10"
                wire:keydown.enter.prevent="searchRepeater('{{ $field['name'] }}')"
              >
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
            </div>
            <button
              wire:click="searchRepeater('{{ $field['name'] }}')"
              class="ml-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            >
              Search
            </button>
          </div>

          <!-- Repeater table -->
          <table class="w-full border-collapse border border-gray-300 mb-4">
            <thead>
            <tr>
              @foreach($field['subfields'] as $subfield)
                <th class="border border-gray-300 p-2 bg-gray-100">{{ $subfield['label'] }}</th>
              @endforeach
              <th class="border border-gray-300 p-2 bg-gray-100">Actions</th>
            </tr>
            </thead>
            <tbody>
            @php
              $paginatedRows = $this->getPaginatedRepeaterRows($field['name']);
              $rowsCount = count($paginatedRows);
              @endphp

            @if($rowsCount === 0)
              <tr>
                <td colspan="{{ count($field['subfields']) + 1 }}" class="border border-gray-300 p-4 text-center text-gray-500">
                  @if(empty($repeaterSearch[$field['name']]))
                    No rows available. Click "Add Row" to create one.
                  @else
                    No rows match your search query.
                  @endif
                </td>
              </tr>
            @else
              @foreach($paginatedRows as $realIndex => $repeaterItem)
                <tr class="repeater-row">
                  @foreach($field['subfields'] as $subfieldIndex => $subfield)
                    @php
                      // Create a copy of the subfield to avoid reference issues
                      $currentSubfield = $subfield;
                      $currentSubfield['key'] = sprintf("availablePropertiesData.%s.%s.%s", $field['name'], $realIndex, $subfield['name']);
                      @endphp
                    <td class="border border-gray-300 p-2">
                      @if($currentSubfield['type'] == 'text')
                        @include('Larafields::components.TextField', ['field' => $currentSubfield])
                      @endif

                      @if($currentSubfield['type'] == 'number')
                        @include('Larafields::components.NumberField', ['field' => $currentSubfield])
                      @endif

                      @if($currentSubfield['type'] == 'textarea')
                        @include('Larafields::components.TextareaField', ['field' => $currentSubfield])
                      @endif

                      @if($currentSubfield['type'] == 'datetime')
                        @include('Larafields::components.DateTimeField', ['field' => $currentSubfield])
                      @endif

                      @if($currentSubfield['type'] == 'date')
                        @include('Larafields::components.DateField', ['field' => $currentSubfield])
                      @endif

                      @if($currentSubfield['type'] == 'week')
                        @include('Larafields::components.WeekField', ['field' => $currentSubfield])
                      @endif

                      @if($currentSubfield['type'] == 'month')
                        @include('Larafields::components.MonthField', ['field' => $currentSubfield])
                      @endif

                      @if($currentSubfield['type'] == 'file')
                        @include('Larafields::components.FileField', ['field' => $currentSubfield])
                      @endif

                      @if($currentSubfield['type'] == 'multiselect')
                          <x-tom-select
                            class="multiselect"
                            wire:model="{!! sprintf('availablePropertiesData.%s.%s.%s', $field['name'], $realIndex, $currentSubfield['name']) !!}"
                            options="{!! sprintf('availablePropertiesSchema.%s.subfields.%s.options', $key, $subfieldIndex) !!}"
                            key="ms{{$key}}{{$realIndex}}"
                            :create="($currentSubfield['custom_values'] ?? false) ? true : null"
                            multiple
                          />
                      @endif

                      @if($currentSubfield['type'] == 'select')
                        <x-tom-select
                          wire:model="{!! sprintf('availablePropertiesData.%s.%s.%s', $field['name'], $realIndex, $currentSubfield['name']) !!}"
                          options="{!! sprintf('availablePropertiesSchema.%s.subfields.%s.options', $key, $subfieldIndex) !!}"
                          key="ms{{$realIndex}}"
                          :create="($currentSubfield['custom_values'] ?? false) ? true : null"
                          wire:ignore
                        />
                      @endif
                    </td>
                  @endforeach
                  <td class="border border-gray-300 p-2 text-center">
                    <button
                      wire:click.prevent="removeRepeaterRow('{{ $field['name'] }}', {{ $realIndex }})"
                      class="text-red-500 hover:text-red-700">
                      Remove
                    </button>
                  </td>
                </tr>
              @endforeach
            @endif
            </tbody>
          </table>

          <!-- Pagination controls -->
          @if($repeaterPagination[$field['name']]['totalPages'] > 1)
            <div class="flex items-center justify-between mb-4">
              <div class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ ($repeaterPagination[$field['name']]['currentPage'] - 1) * $itemsPerPage + 1 }}</span>
                to
                <span class="font-medium">
                  {{ min($repeaterPagination[$field['name']]['currentPage'] * $itemsPerPage, $repeaterPagination[$field['name']]['totalItems']) }}
                </span>
                of
                <span class="font-medium">{{ $repeaterPagination[$field['name']]['totalItems'] }}</span>
                rows
              </div>
              <div class="flex space-x-2">
                <button
                  wire:click="changePage('{{ $field['name'] }}', {{ $repeaterPagination[$field['name']]['currentPage'] - 1 }})"
                  class="px-3 py-1 rounded border border-gray-300 {{ $repeaterPagination[$field['name']]['currentPage'] <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100' }}"
                  {{ $repeaterPagination[$field['name']]['currentPage'] <= 1 ? 'disabled' : '' }}
                >
                  Previous
                </button>

                @php
                  $currentPage = $repeaterPagination[$field['name']]['currentPage'];
                  $totalPages = $repeaterPagination[$field['name']]['totalPages'];
                  $range = 2; // Show 2 pages before and after current page

                  $startPage = max(1, $currentPage - $range);
                  $endPage = min($totalPages, $currentPage + $range);

                  // Always show first page
                  if ($startPage > 1) {
                    echo '<button wire:click="changePage(\'' . $field['name'] . '\', 1)" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100">1</button>';

                    // Show ellipsis if there's a gap
                    if ($startPage > 2) {
                      echo '<span class="px-3 py-1">...</span>';
                    }
                  }

                  // Show page numbers
                  for ($i = $startPage; $i <= $endPage; $i++) {
                    $isCurrentPage = $i === $currentPage;
                    $buttonClass = $isCurrentPage
                      ? 'px-3 py-1 rounded border border-blue-500 bg-blue-500 text-white'
                      : 'px-3 py-1 rounded border border-gray-300 hover:bg-gray-100';

                    echo '<button wire:click="changePage(\'' . $field['name'] . '\', ' . $i . ')" class="' . $buttonClass . '">' . $i . '</button>';
                  }

                  // Always show last page
                  if ($endPage < $totalPages) {
                    // Show ellipsis if there's a gap
                    if ($endPage < $totalPages - 1) {
                      echo '<span class="px-3 py-1">...</span>';
                    }

                    echo '<button wire:click="changePage(\'' . $field['name'] . '\', ' . $totalPages . ')" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100">' . $totalPages . '</button>';
                  }
                @endphp

                <button
                  wire:click="changePage('{{ $field['name'] }}', {{ $repeaterPagination[$field['name']]['currentPage'] + 1 }})"
                  class="px-3 py-1 rounded border border-gray-300 {{ $repeaterPagination[$field['name']]['currentPage'] >= $repeaterPagination[$field['name']]['totalPages'] ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100' }}"
                  {{ $repeaterPagination[$field['name']]['currentPage'] >= $repeaterPagination[$field['name']]['totalPages'] ? 'disabled' : '' }}
                >
                  Next
                </button>
              </div>
            </div>
          @endif

          <button wire:click.prevent="addRepeaterRow('{{ $field['name'] }}')"
                  class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Row
          </button>
        </div>
      @endif

    </div>
  @endforeach

  @if(Session::has('message'))
    <hr>
    <p>{{ session('message') }}</p>
  @endif

  <button
    wire:click.prevent.debounce.500ms="submit"
    wire:loading.attr="disabled"
    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
  >
    <span wire:loading.remove>Submit</span>
    <span wire:loading>Saving...</span>
  </button>
</div>
