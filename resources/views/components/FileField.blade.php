@php use Illuminate\Support\Facades\Storage;use Illuminate\Support\Str;use Livewire\Features\SupportFileUploads\TemporaryUploadedFile; @endphp
<div class="lftw:w-full">
  <label for="{{ $field['key'] }}" class="lftw:block lftw:cursor-pointer">
    <div class="lftw:border lftw:border-gray-300 lftw:bg-gray-50 lftw:p-4 lftw:text-center lftw:hover:bg-gray-100 lftw:transition-colors lftw:duration-200">
      @if($src = data_get($this, $field['key']))
        @if ( !is_null($src) && !is_object($src) )
          @php
            $mimeType = Storage::mimeType($src);
            $isPreviewable = Str::startsWith($mimeType, ['image/', 'video/']);
          @endphp

          @if($isPreviewable)
            <img class="lftw:w-32 lftw:h-32 lftw:object-cover lftw:mx-auto lftw:mb-3 lftw:border lftw:border-gray-200"
                 src="data:{{ $mimeType }};base64,{{ base64_encode(Storage::disk()->get($src)) }}">
          @else
            <div class="lftw:flex lftw:items-center lftw:justify-center lftw:w-32 lftw:h-32 lftw:mx-auto lftw:mb-3 lftw:bg-gray-200 lftw:border lftw:border-gray-300">
              <svg class="lftw:w-8 lftw:h-8 lftw:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
              </svg>
            </div>
            <p class="lftw:text-sm lftw:text-gray-600 lftw:mb-2">{{ basename($src) }}</p>
          @endif

        @elseif( get_class($src) == TemporaryUploadedFile::class && $src->isPreviewable() )
          <img class="lftw:w-32 lftw:h-32 lftw:object-cover lftw:mx-auto lftw:mb-3 lftw:border lftw:border-gray-200" 
               src="data:{{ $src->getMimeType() }};base64,{{ base64_encode($src->get()) }}">
        @endif

        <div class="lftw:flex lftw:items-center lftw:justify-center lftw:gap-2">
          <svg class="lftw:w-4 lftw:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
          </svg>
          <span class="lftw:text-sm lftw:font-medium lftw:text-gray-700">Change file</span>
        </div>
      @else
        <div class="lftw:flex lftw:flex-col lftw:items-center lftw:gap-2">
          <svg class="lftw:w-8 lftw:h-8 lftw:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
          </svg>
          <span class="lftw:text-sm lftw:font-medium lftw:text-gray-700">Click to upload file</span>
          <span class="lftw:text-xs lftw:text-gray-500">Choose from your computer</span>
        </div>
      @endif
    </div>
  </label>

  <input
    wire:model="{{ $field['key'] }}"
    id="{{ $field['key'] }}"
    type="file"
    name="{{ $field['name'] }}"
    @required($field['required'] ?? false)
    class="lftw:hidden"
  />
</div>
