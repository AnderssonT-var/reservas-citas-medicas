@props(['estado'])
<span {{ $attributes->merge(['class' => 'chip']) }} data-tono="{{ $estado->tono() }}">{{ $estado->label() }}</span>
