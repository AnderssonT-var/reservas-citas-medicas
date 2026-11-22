// Editor de turnos semanales: agregar y quitar filas.
export function iniciarTurnos() {
  document.querySelectorAll('[data-turnos]').forEach((caja) => {
    const lista = caja.querySelector('[data-turnos-lista]');
    const plantilla = caja.querySelector('template');
    let indice = lista.children.length;

    const renumerar = () => {
      caja.querySelector('[data-turnos-vacio]')?.toggleAttribute('hidden', lista.children.length > 0);
    };

    caja.querySelector('[data-turnos-agregar]').addEventListener('click', () => {
      const fila = plantilla.content.firstElementChild.cloneNode(true);
      fila.querySelectorAll('[name]').forEach((el) => (el.name = el.name.replace('__i__', indice)));
      fila.querySelectorAll('[id]').forEach((el) => (el.id = el.id.replace('__i__', indice)));
      fila.querySelectorAll('label[for]').forEach((el) => (el.htmlFor = el.htmlFor.replace('__i__', indice)));
      // Copia el día del turno anterior para encadenar rápido mañana y tarde.
      const ultima = lista.lastElementChild;
      if (ultima) {
        fila.querySelector('select').value = ultima.querySelector('select').value;
        fila.querySelector('[name$="[hora_inicio]"]').value = ultima.querySelector('[name$="[hora_fin]"]').value || '';
      }
      indice++;
      lista.append(fila);
      fila.querySelector('select').focus();
      renumerar();
    });

    lista.addEventListener('click', (e) => {
      const quitar = e.target.closest('[data-turnos-quitar]');
      if (!quitar) return;
      const fila = quitar.closest('.shift-row');
      const siguiente = fila.nextElementSibling ?? fila.previousElementSibling;
      fila.remove();
      (siguiente?.querySelector('select') ?? caja.querySelector('[data-turnos-agregar]')).focus();
      renumerar();
    });
    renumerar();
  });
}
