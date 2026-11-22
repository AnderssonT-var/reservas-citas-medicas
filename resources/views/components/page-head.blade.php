@props(['title', 'subtitle' => null, 'back' => null, 'backLabel' => 'Volver'])
<header class="page-head">
    <div>
        @if($back)<a href="{{ $back }}" class="back"><x-icon name="arrow-left" size="16"/> {{ $backLabel }}</a>@endif
        <h1>{{ $title }}</h1>
        @if($subtitle)<p>{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)<div class="cluster">{{ $actions }}</div>@endisset
</header>
