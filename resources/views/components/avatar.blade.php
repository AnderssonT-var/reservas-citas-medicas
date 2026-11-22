@props(['user', 'color' => null, 'size' => null])
<span {{ $attributes->class(['avatar', $size]) }} @if($color) style="--avatar-bg:color-mix(in oklab, {{ $color }} 15%, var(--surface));--avatar-ink:{{ $color }}" @endif aria-hidden="true">{{ $user->iniciales() }}</span>
