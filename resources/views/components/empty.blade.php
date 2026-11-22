@props(['icon' => 'calendar', 'title'])
<div class="empty">
    <x-icon :name="$icon" size="40"/>
    <h3>{{ $title }}</h3>
    @if($slot->isNotEmpty())<div class="stack" style="--gap:.75rem;align-items:center">{{ $slot }}</div>@endif
</div>
