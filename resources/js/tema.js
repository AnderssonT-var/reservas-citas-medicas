// Tres estados: sistema (sin valor guardado), claro u oscuro.
const meta = () => document.querySelector('meta[name="color-scheme"]');

export function aplicarTema(valor) {
  try {
    if (valor === 'light' || valor === 'dark') localStorage.setItem('color-scheme', valor);
    else localStorage.removeItem('color-scheme');
  } catch {}
  meta().content = valor === 'light' || valor === 'dark' ? valor : 'light dark';
}

export function iniciarTema() {
  let actual = 'system';
  try { actual = localStorage.getItem('color-scheme') ?? 'system'; } catch {}

  document.querySelectorAll('input[name="tema"]').forEach((r) => {
    r.checked = r.value === actual;
    r.addEventListener('change', () => aplicarTema(r.value));
  });

  window.addEventListener('storage', (e) => {
    if (e.key === 'color-scheme') meta().content = e.newValue ?? 'light dark';
  });

  const etiqueta = document.querySelector('[data-tema-sistema]');
  if (etiqueta) {
    const mq = matchMedia('(prefers-color-scheme: dark)');
    const pinta = () => (etiqueta.textContent = mq.matches ? 'ahora oscuro' : 'ahora claro');
    pinta();
    mq.addEventListener('change', pinta);
  }
}
