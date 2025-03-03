@php use Illuminate\Support\Facades\Storage;use Livewire\Features\SupportFileUploads\TemporaryUploadedFile; @endphp
<div>
  <label for="{{ $field['key'] }}">
    @if($src = data_get($this, $field['key']))
      @if ( !is_null($src) && !is_object($src) )
        <img width="150" height="150" src="data:{{ Storage::mimeType($src) }};base64,{{ base64_encode(Storage::disk()->get($src)) }}">
      @elseif( get_class($src) == TemporaryUploadedFile::class  )
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
