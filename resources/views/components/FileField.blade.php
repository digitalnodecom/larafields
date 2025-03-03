@php use Illuminate\Support\Facades\Storage;use Illuminate\Support\Str;use Livewire\Features\SupportFileUploads\TemporaryUploadedFile; @endphp
<div>
  <label for="{{ $field['key'] }}">
    @if($src = data_get($this, $field['key']))
      @if ( !is_null($src) && !is_object($src) )
        @php
          $mimeType = Storage::mimeType($src);
          $isPreviewable = Str::startsWith($mimeType, ['image/', 'video/']);
        @endphp

        @if($isPreviewable)
          <img width="150" height="150"
               src="data:{{ $mimeType }};base64,{{ base64_encode(Storage::disk()->get($src)) }}">
        @else
          File exists: {{ Storage::url($src) }}
        @endif
      @elseif( get_class($src) == TemporaryUploadedFile::class && $src->isPreviewable() )
        <img width="150" height="150" src="data:{{ $src->getMimeType() }};base64,{{ base64_encode($src->get()) }}">
      @endif
    @endif
  </label>

  <input
    wire:model="{{ $field['key'] }}"
    id="{{ $field['key'] }}"
    type="file"
    name="{{ $field['name'] }}"
    @required($field['required'] ?? false)
  />
</div>
