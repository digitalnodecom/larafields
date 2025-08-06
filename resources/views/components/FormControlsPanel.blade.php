@props(['hasRepeaters' => false])

<div class="lftw:bg-white lftw:border lftw:border-gray-200 lftw:p-4 lftw:mb-6">
    <div class="lftw:flex lftw:flex-col lftw:lg:flex-row lftw:gap-4 lftw:items-start lftw:lg:items-center lftw:justify-between">
        
        <!-- Search Section -->
        @if($hasRepeaters)
            <div class="lftw:flex lftw:flex-col lftw:sm:flex-row lftw:gap-3 lftw:flex-grow lftw:max-w-md">
                <div class="lftw:text-sm lftw:font-medium lftw:text-gray-700 lftw:mb-1 lftw:sm:mb-0 lftw:sm:self-center lftw:whitespace-nowrap">
                    Search All:
                </div>
                <div class="lftw:relative lftw:flex-grow">
                    <input
                        type="text"
                        wire:model="globalSearch"
                        placeholder="Search across all repeater fields..."
                        wire:keydown.enter.prevent="performGlobalSearch"
                        class="lftw:w-full lftw:pl-10 lftw:pr-4 lftw:py-2 lftw:border lftw:border-gray-300 lftw:bg-white lftw:text-gray-900 lftw:placeholder-gray-500 lftw:focus:outline-none lftw:focus:ring-2 lftw:focus:ring-brand-500 lftw:focus:border-brand-500 lftw:transition-colors lftw:duration-200"
                    >
                    <div class="lftw:absolute lftw:inset-y-0 lftw:left-0 lftw:pl-3 lftw:flex lftw:items-center lftw:pointer-events-none">
                        <svg class="lftw:h-4 lftw:w-4 lftw:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
                <button
                    wire:click="performGlobalSearch"
                    class="lftw:px-4 lftw:py-2 lftw:bg-gray-600 lftw:text-white lftw:font-medium lftw:hover:bg-gray-700 lftw:focus:outline-none lftw:focus:ring-2 lftw:focus:ring-gray-500 lftw:focus:ring-offset-2 lftw:transition-colors lftw:duration-200 lftw:whitespace-nowrap"
                >
                    Search
                </button>
            </div>
        @endif

        <!-- Export Section -->
        <div class="lftw:flex lftw:flex-col lftw:sm:flex-row lftw:gap-3 lftw:items-start lftw:sm:items-center">
            <div class="lftw:text-sm lftw:font-medium lftw:text-gray-700 lftw:mb-1 lftw:sm:mb-0 lftw:whitespace-nowrap">
                Export:
            </div>
            <button
                wire:click.prevent="exportCsv"
                wire:loading.attr="disabled"
                class="lftw:px-4 lftw:py-2 lftw:bg-gray-600 lftw:text-white lftw:font-medium lftw:hover:bg-gray-700 lftw:focus:outline-none lftw:focus:ring-2 lftw:focus:ring-gray-500 lftw:focus:ring-offset-2 lftw:disabled:opacity-50 lftw:disabled:cursor-not-allowed lftw:transition-colors lftw:duration-200 lftw:flex lftw:items-center lftw:gap-2 lftw:whitespace-nowrap"
                title="Download form data as CSV"
            >
                <svg class="lftw:w-4 lftw:h-4 lftw:flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span wire:loading.remove wire:target="exportCsv">CSV</span>
                <span wire:loading wire:target="exportCsv" class="lftw:flex lftw:items-center lftw:gap-2">
                    <svg class="lftw:w-4 lftw:h-4 lftw:animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="lftw:opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="lftw:opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Generating...
                </span>
            </button>
        </div>

    </div>
</div>