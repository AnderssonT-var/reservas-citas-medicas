// Asistente de reserva: especialidad → doctor → día → hora → motivo.
// Lo usan el paciente (/mi/reservar) y la administración (/admin/citas/create).

const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
const aFecha = (iso) => { const [y, m, d] = iso.split('-').map(Number); return new Date(y, m - 1, d); };
const fmt = (opts) => new Intl.DateTimeFormat('es-EC', opts);
const diaCorto = fmt({ weekday: 'short' });
const diaLargo = fmt({ weekday: 'long', day: 'numeric', month: 'long' });
const dinero = new Intl.NumberFormat('es-EC', { style: 'currency', currency: 'USD' });

async function obtener(url, señal) {
  const r = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, signal: señal });
  if (!r.ok) throw new Error(`HTTP ${r.status}`);
  return r.json();
}

export function iniciarReserva() {
  const raiz = document.querySelector('[data-reserva]');
  if (!raiz) return;

  const api = {
    doctores: raiz.dataset.apiDoctores,
    dias: raiz.dataset.apiDias,
    bloques: raiz.dataset.apiBloques,
  };

  const $ = (s) => raiz.querySelector(s);
  const pasos = {
    doctor: $('[data-paso="doctor"]'),
    dia: $('[data-paso="dia"]'),
    hora: $('[data-paso="hora"]'),
    motivo: $('[data-paso="motivo"]'),
  };
  const cajaDoctores = $('[data-doctores]');
  const cajaDias = $('[data-dias]');
  const cajaBloques = $('[data-bloques]');
  const stub = $('[data-stub]');
  const enviar = $('[data-enviar]');
  const input = {
    doctor: $('input[name=doctor_id]'),
    fecha: $('input[name=fecha]'),
    hora: $('input[name=hora]'),
  };

  const estado = { especialidad: null, doctor: null, fecha: null, hora: null, doctores: [] };
  let control;

  const bloquear = (paso, si) => {
    paso.classList.toggle('locked', si);
    paso.classList.remove('done');
    paso.toggleAttribute('inert', si);
  };
  const hecho = (paso) => paso.classList.add('done');

  const cargando = (caja, n, alto = 64) => {
    caja.innerHTML = Array.from({ length: n }, () => `<div class="skeleton" style="block-size:${alto}px"></div>`).join('');
  };

  const marcar = (grupo, boton) => {
    grupo.querySelectorAll('[aria-pressed]').forEach((b) => b.setAttribute('aria-pressed', String(b === boton)));
  };

  function pintarResumen() {
    const d = estado.doctores.find((x) => x.id === estado.doctor);
    const fila = (k, v) => { const el = stub.querySelector(`[data-r="${k}"]`); el.textContent = v ?? 'Sin elegir'; el.classList.toggle('empty-val', !v); };
    fila('especialidad', d?.especialidad ?? raiz.querySelector('.pill[aria-pressed="true"]')?.dataset.nombre);
    fila('doctor', d?.nombre);
    fila('consultorio', d?.consultorio);
    fila('fecha', estado.fecha ? diaLargo.format(aFecha(estado.fecha)) : null);
    fila('tarifa', d ? dinero.format(d.tarifa) : null);
    const grande = stub.querySelector('[data-r="hora"]');
    grande.textContent = estado.hora ?? '--:--';
    const listo = Boolean(estado.doctor && estado.fecha && estado.hora);
    stub.classList.toggle('ready', listo);
    enviar.disabled = !listo;
    input.doctor.value = estado.doctor ?? '';
    input.fecha.value = estado.fecha ?? '';
    input.hora.value = estado.hora ?? '';
    if (listo) { stub.classList.remove('pop'); void stub.offsetWidth; stub.classList.add('pop'); }
  }

  async function elegirEspecialidad(id, boton) {
    estado.especialidad = id; estado.doctor = null; estado.fecha = null; estado.hora = null;
    if (boton) marcar(boton.parentElement, boton);
    bloquear(pasos.doctor, false); bloquear(pasos.dia, true); bloquear(pasos.hora, true); bloquear(pasos.motivo, true);
    pintarResumen();
    control?.abort(); control = new AbortController();
    cargando(cajaDoctores, 2);
    try {
      const url = new URL(api.doctores, location.href);
      if (id) url.searchParams.set('especialidad', id);
      estado.doctores = await obtener(url, control.signal);
    } catch (e) { if (e.name === 'AbortError') return; cajaDoctores.innerHTML = '<p class="notice error">No pudimos cargar los doctores. Recarga la página.</p>'; return; }

    if (!estado.doctores.length) {
      cajaDoctores.innerHTML = '<p class="muted">No hay doctores con horario en esta especialidad por ahora.</p>';
      return;
    }
    cajaDoctores.innerHTML = estado.doctores.map((d) => `
      <button type="button" class="doc-option" aria-pressed="false" data-id="${d.id}">
        <span class="avatar" style="--avatar-bg:color-mix(in oklab, ${esc(d.color)} 16%, var(--surface));--avatar-ink:${esc(d.color)}">${esc(d.iniciales)}</span>
        <span><b>${esc(d.nombre)}</b><span>${esc(d.especialidad)}, ${d.duracion} min${d.consultorio ? ', ' + esc(d.consultorio) : ''}</span></span>
      </button>`).join('');
    const pre = Number(raiz.dataset.preDoctor);
    if (pre) { raiz.dataset.preDoctor = ''; cajaDoctores.querySelector(`[data-id="${pre}"]`)?.click(); }
  }

  async function elegirDoctor(id, boton) {
    estado.doctor = id; estado.fecha = null; estado.hora = null;
    marcar(cajaDoctores, boton);
    hecho(pasos.doctor);
    bloquear(pasos.dia, false); bloquear(pasos.hora, true); bloquear(pasos.motivo, true);
    pintarResumen();
    control?.abort(); control = new AbortController();
    cajaDias.innerHTML = Array.from({ length: 7 }, () => '<div class="skeleton" style="flex:0 0 66px;block-size:78px"></div>').join('');
    let dias;
    try { dias = await obtener(api.dias.replace('__id__', id), control.signal); }
    catch (e) { if (e.name === 'AbortError') return; cajaDias.innerHTML = '<p class="notice error">No pudimos cargar la agenda.</p>'; return; }

    if (!dias.length) { cajaDias.innerHTML = '<p class="muted">Este doctor no tiene días de atención en las próximas semanas.</p>'; return; }
    cajaDias.innerHTML = dias.map((d) => {
      const f = aFecha(d.fecha);
      const lleno = d.libres === 0;
      return `<button type="button" class="date-btn" aria-pressed="false" data-fecha="${d.fecha}" ${lleno ? 'disabled' : ''} aria-label="${esc(diaLargo.format(f))}, ${lleno ? 'sin cupos' : d.libres + ' horarios libres'}">
        <small>${esc(diaCorto.format(f).replace('.', ''))}</small><b>${f.getDate()}</b><i>${lleno ? 'lleno' : d.libres + ' libres'}</i></button>`;
    }).join('');
    cajaDias.querySelector('.date-btn:not(:disabled)')?.focus({ preventScroll: true });
    pasos.dia.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'nearest' });
  }

  async function elegirFecha(fecha, boton) {
    estado.fecha = fecha; estado.hora = null;
    marcar(cajaDias, boton);
    hecho(pasos.dia);
    bloquear(pasos.hora, false); bloquear(pasos.motivo, true);
    pintarResumen();
    control?.abort(); control = new AbortController();
    cargando(cajaBloques, 8, 52);
    let bloques;
    try {
      const url = new URL(api.bloques.replace("__id__", estado.doctor), location.href);
      url.searchParams.set('fecha', fecha);
      bloques = await obtener(url, control.signal);
    } catch (e) { if (e.name === 'AbortError') return; cajaBloques.innerHTML = '<p class="notice error">No pudimos cargar los horarios.</p>'; return; }

    const titulo = pasos.hora.querySelector('[data-mes]');
    if (titulo) titulo.textContent = `${diaLargo.format(aFecha(fecha))}`;
    if (!bloques.some((b) => b.disponible)) { cajaBloques.innerHTML = '<p class="muted">Ese día ya no tiene horarios libres. Prueba con otro.</p>'; return; }
    cajaBloques.innerHTML = bloques.map((b) => `
      <button type="button" class="slot" aria-pressed="false" data-hora="${b.inicio}" ${b.disponible ? '' : 'disabled aria-label="' + b.inicio + ', ocupado"'}>
        <b>${b.inicio}</b><span>${b.disponible ? 'hasta ' + b.fin : 'ocupado'}</span>
      </button>`).join('');
  }

  function elegirHora(hora, boton) {
    estado.hora = hora;
    marcar(cajaBloques, boton);
    hecho(pasos.hora);
    bloquear(pasos.motivo, false);
    pintarResumen();
  }

  raiz.addEventListener('click', (e) => {
    const pill = e.target.closest('.pill[data-especialidad]');
    if (pill) return elegirEspecialidad(Number(pill.dataset.especialidad) || null, pill);
    const doc = e.target.closest('.doc-option');
    if (doc) return elegirDoctor(Number(doc.dataset.id), doc);
    const dia = e.target.closest('.date-btn');
    if (dia && !dia.disabled) return elegirFecha(dia.dataset.fecha, dia);
    const slot = e.target.closest('.slot[data-hora]');
    if (slot && !slot.disabled) return elegirHora(slot.dataset.hora, slot);
  });

  // Evita doble envío.
  raiz.closest('form')?.addEventListener('submit', () => {
    enviar.disabled = true;
    enviar.textContent = 'Reservando…';
  });

  // Estado inicial
  [pasos.dia, pasos.hora, pasos.motivo].forEach((p) => bloquear(p, true));
  const pre = Number(raiz.dataset.preEspecialidad);
  const pill = pre ? raiz.querySelector(`.pill[data-especialidad="${pre}"]`) : raiz.querySelector('.pill[data-especialidad]');
  if (pill) elegirEspecialidad(Number(pill.dataset.especialidad) || null, pill);
  pintarResumen();
}
