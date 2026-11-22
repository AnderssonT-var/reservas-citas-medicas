// Toasts con popover="manual" dentro de una región fija.
const icono = {
  ok: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>',
  error: '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5M12 16.5v.01"/></svg>',
};

let region;

export function toast(mensaje, tipo = 'ok', ms = 5000) {
  region ??= document.getElementById('toasts');
  if (!region) return;
  const el = document.createElement('div');
  el.className = `toast ${tipo === 'error' ? 'error' : ''}`;
  el.setAttribute('popover', 'manual');
  el.innerHTML = `<span class="ico">${icono[tipo] ?? icono.ok}</span><p></p><button type="button" class="btn btn-ghost btn-sm btn-icon" aria-label="Cerrar aviso"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg></button>`;
  el.querySelector('p').textContent = mensaje;
  region.append(el);
  el.showPopover?.();

  if ('ariaNotify' in document) document.ariaNotify(mensaje);
  else {
    const live = document.getElementById('anuncios');
    if (live) live.textContent = mensaje;
  }

  let timer;
  const cerrar = () => {
    clearTimeout(timer);
    const enfocado = el.contains(document.activeElement);
    el.hidePopover?.();
    setTimeout(() => el.remove(), 300);
    if (enfocado) document.querySelector('main')?.focus();
  };
  const programar = () => { timer = setTimeout(cerrar, ms); };
  el.querySelector('button').addEventListener('click', cerrar);
  // No se cierra mientras el foco o el puntero están dentro.
  el.addEventListener('focusin', () => clearTimeout(timer));
  el.addEventListener('focusout', programar);
  el.addEventListener('pointerenter', () => clearTimeout(timer));
  el.addEventListener('pointerleave', programar);
  programar();
}

export function iniciarToasts() {
  document.querySelectorAll('template[data-toast]').forEach((t) => {
    toast(t.content.textContent.trim(), t.dataset.toast);
    t.remove();
  });
  window.toast = toast;
}
