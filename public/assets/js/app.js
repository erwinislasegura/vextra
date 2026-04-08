(function () {
  const base = (window.APP_BASE_PATH || '').replace(/\/$/, '');

  function normalizarInterna(url) {
    if (!url || !url.startsWith('/')) return url;
    if (url.startsWith('//')) return url;
    if (!base) return url;
    if (url === base || url.startsWith(base + '/')) return url;
    return base + url;
  }

  document.querySelectorAll('a[href]').forEach((el) => {
    const href = el.getAttribute('href');
    if (href && href.startsWith('/')) {
      el.setAttribute('href', normalizarInterna(href));
    }
  });

  document.querySelectorAll('form[action]').forEach((el) => {
    const action = el.getAttribute('action');
    if (action && action.startsWith('/')) {
      el.setAttribute('action', normalizarInterna(action));
    }
  });

  document.querySelectorAll('[data-confirmar]').forEach((el) => {
    el.addEventListener('click', (e) => {
      if (!confirm(el.dataset.confirmar || '¿Confirmas esta acción?')) {
        e.preventDefault();
      }
    });
  });

  // UX transversal de formularios: marca campos requeridos y evita doble envío.
  document.querySelectorAll('form').forEach((form) => {
    form.querySelectorAll('[required]').forEach((campo) => {
      const id = campo.getAttribute('id');
      if (!id) return;
      const label = form.querySelector(`label[for="${id}"]`);
      if (!label) return;
      if (label.querySelector('.campo-requerido')) return;
      const marcador = document.createElement('span');
      marcador.className = 'campo-requerido';
      marcador.textContent = '*';
      label.appendChild(marcador);
    });

    form.addEventListener('submit', () => {
      if (!form.checkValidity()) {
        return;
      }

      form.classList.add('form-enviando');
      form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((btn) => {
        if (btn.dataset.locked === '1') return;
        btn.dataset.locked = '1';
        if (!btn.dataset.originalText) {
          btn.dataset.originalText = btn.tagName === 'INPUT' ? (btn.value || '') : (btn.innerHTML || '');
        }
        if (btn.tagName === 'INPUT') {
          btn.value = 'Guardando...';
        } else {
          btn.innerHTML = 'Guardando...';
        }
        btn.disabled = true;
      });
    });
  });
})();
