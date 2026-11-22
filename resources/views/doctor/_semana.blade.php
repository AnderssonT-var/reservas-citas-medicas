<nav class="week" aria-label="Semana">
    @foreach($semana as $d)
        <a href="{{ route('doctor.agenda', ['fecha' => $d['fecha']->toDateString()]) }}" class="day {{ $d['atiende'] ? '' : 'off' }} {{ $d['fecha']->isToday() ? 'today' : '' }}" @if($d['fecha']->isSameDay($fecha)) aria-current="date" @endif>
            <small>{{ $d['fecha']->translatedFormat('D') }}</small>
            <b>{{ $d['fecha']->day }}</b>
            <i>{{ $d['atiende'] ? trans_choice('{0} libre|{1} 1 cita|[2,*] :count citas', $d['total']) : 'no atiende' }}</i>
        </a>
    @endforeach
</nav>
