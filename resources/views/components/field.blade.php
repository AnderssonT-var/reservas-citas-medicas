@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'id' => null, 'required' => false, 'wrap' => null])
@php($id = $id ?? 'f-'.str_replace(['[', ']', '.'], '-', $name))
@php($key = str_replace(['[', ']'], ['.', ''], $name))
<div @class(['field', $wrap])>
    <label for="{{ $id }}">{{ $label }}@if(!$required)<span class="muted" style="font-weight:400"> (opcional)</span>@endif</label>
    @if($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'textarea']) }} @error($key) aria-invalid="true" aria-describedby="{{ $id }}-e" @enderror @required($required)>{{ old($key, $value) }}</textarea>
    @elseif($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'select']) }} @error($key) aria-invalid="true" aria-describedby="{{ $id }}-e" @enderror @required($required)>{{ $slot }}</select>
    @else
        <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}" value="{{ $type === 'password' ? '' : old($key, $value) }}" {{ $attributes->merge(['class' => 'input']) }} @error($key) aria-invalid="true" aria-describedby="{{ $id }}-e" @enderror @required($required)>
    @endif
    @if($hint)<span class="hint">{{ $hint }}</span>@endif
    @error($key)<span class="error" id="{{ $id }}-e">{{ $message }}</span>@enderror
</div>
