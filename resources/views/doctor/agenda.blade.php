<x-layouts.app title="Agenda">
    <x-page-head title="Agenda" :subtitle="ucfirst($fecha->translatedFormat('l j \d\e F Y'))">
        <x-slot:actions>
            <a class="btn btn-icon" href="{{ route('doctor.agenda', ['fecha' => $fecha->subWeek()->toDateString()]) }}" aria-label="Semana anterior"><x-icon name="chevron-left"/></a>
            @unless($fecha->isToday())<a class="btn" href="{{ route('doctor.agenda') }}">Hoy</a>@endunless
            <a class="btn btn-icon" href="{{ route('doctor.agenda', ['fecha' => $fecha->addWeek()->toDateString()]) }}" aria-label="Semana siguiente"><x-icon name="chevron-right"/></a>
        </x-slot:actions>
    </x-page-head>
    @include('doctor._semana')
    <section class="card mt-3">
        <div class="card-head"><h2>{{ $citas->count() }} {{ $citas->count() === 1 ? 'cita' : 'citas' }}</h2><p>Las canceladas no se muestran</p></div>
        @include('doctor._agenda')
    </section>
</x-layouts.app>
