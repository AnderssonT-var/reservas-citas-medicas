import { iniciarTema } from './tema';
import { iniciarToasts } from './toasts';
import { iniciarConfirmaciones } from './confirmar';
import { iniciarTurnos } from './turnos';
import { iniciarReserva } from './reserva';

// Popover polyfill solo donde falta soporte nativo.
if (!Object.hasOwn(HTMLElement.prototype, 'popover')) {
  import('@oddbird/popover-polyfill');
}

const listo = (fn) => (document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', fn) : fn());

listo(() => {
  iniciarTema();
  iniciarToasts();
  iniciarConfirmaciones();
  iniciarTurnos();
  iniciarReserva();

  // Cuentas demo en el login.
  document.querySelectorAll('[data-demo]').forEach((b) =>
    b.addEventListener('click', () => {
      const f = b.closest('form') ?? document.querySelector('form');
      f.querySelector('[name=email]').value = b.dataset.demo;
      f.querySelector('[name=password]').value = 'password';
      f.querySelector('[name=email]').focus();
    }),
  );

  // Autosubmit de filtros tipo select.
  document.querySelectorAll('[data-autosubmit]').forEach((el) =>
    el.addEventListener('change', () => el.form?.requestSubmit()),
  );
});
