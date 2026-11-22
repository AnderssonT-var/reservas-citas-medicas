// Un solo <dialog> para confirmar acciones destructivas o importantes.
// Uso: <form data-confirm="¿Cancelar la cita?" data-confirm-detalle="..." data-confirm-boton="Cancelar cita" data-confirm-motivo>
export function iniciarConfirmaciones() {
  const dialog = document.getElementById('confirmar');
  if (!dialog) return;
  const titulo = dialog.querySelector('#confirmar-titulo');
  const detalle = dialog.querySelector('[data-detalle]');
  const boton = dialog.querySelector('[value="ok"]');
  const motivo = dialog.querySelector('[data-motivo]');
  let pendiente = null;

  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmado) return;
    e.preventDefault();
    pendiente = form;
    titulo.textContent = form.dataset.confirm;
    detalle.textContent = form.dataset.confirmDetalle ?? '';
    detalle.hidden = !form.dataset.confirmDetalle;
    boton.textContent = form.dataset.confirmBoton ?? 'Confirmar';
    boton.classList.toggle('btn-danger', form.dataset.confirmTono !== 'primario');
    boton.classList.toggle('btn-primary', form.dataset.confirmTono === 'primario');
    motivo.hidden = !('confirmMotivo' in form.dataset);
    motivo.querySelector('textarea').value = '';
    dialog.returnValue = '';
    dialog.showModal();
  });

  dialog.addEventListener('close', () => {
    const valor = dialog.returnValue;
    dialog.returnValue = '';
    if (valor !== 'ok' || !pendiente) { pendiente = null; return; }
    const form = pendiente;
    pendiente = null;
    if (!motivo.hidden) {
      let campo = form.querySelector('[name=motivo_cancelacion]');
      if (!campo) {
        campo = Object.assign(document.createElement('input'), { type: 'hidden', name: 'motivo_cancelacion' });
        form.append(campo);
      }
      campo.value = motivo.querySelector('textarea').value;
    }
    form.dataset.confirmado = '1';
    form.requestSubmit();
  });

  // Click fuera para cerrar donde no existe closedby.
  if (!('closedBy' in HTMLDialogElement.prototype)) {
    const fuera = (e) => {
      const r = dialog.getBoundingClientRect();
      return e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom;
    };
    let afuera = false;
    dialog.addEventListener('pointerdown', (e) => { afuera = fuera(e); });
    dialog.addEventListener('pointerup', (e) => { if (afuera && fuera(e)) dialog.close(); });
  }
}
