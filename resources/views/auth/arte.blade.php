<figure class="auth-art" aria-hidden="true">
    <a href="{{ route('inicio') }}" class="brand" style="color:#fff;padding:0">
        <span class="brand-mark" style="background:#F0A64C;color:#2A1A05"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
        {{ $clinica['nombre_clinica'] }}
    </a>
    <div class="ticket-stack">
        <div class="slot"><b>08:30</b><span>Medicina general</span></div>
        <div class="slot" aria-pressed="true"><b>09:00</b><span>Tu cita, Consultorio 101</span></div>
        <div class="slot"><b>09:30</b><span>Pediatría</span></div>
    </div>
    <div>
        <blockquote>Elige la hora que te sirve. Nosotros nos encargamos del resto.</blockquote>
        <figcaption>Reserva, cancela y revisa tus consultas desde el celular.</figcaption>
    </div>
</figure>
