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

  // Fallback para componentes collapse/accordion cuando Bootstrap JS no está cargado.
  if (!(window.bootstrap && window.bootstrap.Collapse)) {
    const toggles = Array.from(document.querySelectorAll('[data-bs-toggle="collapse"]'));
    toggles.forEach((toggle) => {
      toggle.addEventListener('click', () => {
        const selector = toggle.getAttribute('data-bs-target') || toggle.getAttribute('href');
        if (!selector) return;
        const panel = document.querySelector(selector);
        if (!panel) return;

        const parentSelector = panel.getAttribute('data-bs-parent');
        const willOpen = !panel.classList.contains('show');

        if (parentSelector) {
          const parent = document.querySelector(parentSelector);
          if (parent) {
            parent.querySelectorAll('.accordion-collapse.show').forEach((openPanel) => {
              if (openPanel === panel) return;
              openPanel.classList.remove('show');
              const opener = parent.querySelector('[data-bs-target="#' + openPanel.id + '"]');
              if (opener) {
                opener.classList.add('collapsed');
                opener.setAttribute('aria-expanded', 'false');
              }
            });
          }
        }

        panel.classList.toggle('show', willOpen);
        toggle.classList.toggle('collapsed', !willOpen);
        toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      });
    });
  }

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
      if (form.dataset.asyncChat === '1') {
        return;
      }

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

  // Tablas admin en formato tarjeta para móviles (sin perder acciones).
  function prepararTablasAdminMovil() {
    document.querySelectorAll('.tabla-admin').forEach((tabla) => {
      const encabezados = Array.from(tabla.querySelectorAll('thead th')).map((th) =>
        (th.textContent || '').trim().replace(/\s+/g, ' ')
      );
      if (encabezados.length === 0) return;

      tabla.classList.add('tabla-admin--stack');
      tabla.querySelectorAll('tbody tr').forEach((fila) => {
        Array.from(fila.children).forEach((celda, indice) => {
          if (!(celda instanceof HTMLElement)) return;
          if (!celda.dataset.label) {
            celda.dataset.label = encabezados[indice] || `Campo ${indice + 1}`;
          }
        });
      });
    });
  }

  // Sidebar admin móvil tipo drawer.
  function prepararSidebarAdminMovil() {
    const shell = document.querySelector('.app-shell-admin');
    const sidebar = shell ? shell.querySelector('.sidebar-admin') : null;
    const toggle = document.querySelector('.js-sidebar-toggle');
    if (!shell || !sidebar || !toggle) return;

    const backdrop = document.createElement('button');
    backdrop.type = 'button';
    backdrop.className = 'app-shell-admin__backdrop';
    backdrop.setAttribute('aria-label', 'Cerrar menú');
    shell.appendChild(backdrop);

    const cerrar = () => {
      shell.classList.remove('sidebar-open');
      toggle.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('overflow-hidden');
    };

    const abrir = () => {
      shell.classList.add('sidebar-open');
      toggle.setAttribute('aria-expanded', 'true');
      document.body.classList.add('overflow-hidden');
    };

    toggle.addEventListener('click', () => {
      if (shell.classList.contains('sidebar-open')) {
        cerrar();
      } else {
        abrir();
      }
    });

    backdrop.addEventListener('click', cerrar);
    sidebar.querySelectorAll('a').forEach((enlace) => enlace.addEventListener('click', cerrar));
    window.addEventListener('resize', () => {
      if (window.innerWidth >= 992) cerrar();
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') cerrar();
    });
  }

  prepararTablasAdminMovil();
  prepararSidebarAdminMovil();

  // Habilita instalación PWA para paneles (/app y /admin).
  function prepararInstalacionPwa() {
    const enPanel = /^\/(app|admin)(\/|$)/.test(window.location.pathname || '');
    if (!enPanel) return;
    if (!('serviceWorker' in navigator)) return;
    const yaInstalada = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (yaInstalada) return;

    navigator.serviceWorker.register(normalizarInterna('/sw.js'), { scope: normalizarInterna('/') }).catch(() => null);

    let deferredPrompt = window.__vextraDeferredInstallPrompt || null;
    let botonInstalar = null;
    const esIOS = /iphone|ipad|ipod/i.test(window.navigator.userAgent || '');

    const obtenerBoton = () => {
      if (botonInstalar) return botonInstalar;
      botonInstalar = document.createElement('button');
      botonInstalar.type = 'button';
      botonInstalar.className = 'pwa-install-btn';
      botonInstalar.innerHTML = '<i class="bi bi-download me-1"></i> Instalar app';
      botonInstalar.addEventListener('click', async () => {
        if (deferredPrompt) {
          deferredPrompt.prompt();
          try {
            await deferredPrompt.userChoice;
          } catch (_) {
            // Ignorado
          }
          return;
        }

        const mensaje = esIOS
          ? 'En iPhone/iPad: abre Compartir y luego "Añadir a pantalla de inicio".'
          : 'Si no aparece el popup, usa el menú del navegador y selecciona "Instalar aplicación".';
        alert(mensaje);
      });
      document.body.appendChild(botonInstalar);
      return botonInstalar;
    };

    const mostrarSiDisponible = () => {
      deferredPrompt = window.__vextraDeferredInstallPrompt || deferredPrompt;
      const boton = obtenerBoton();
      boton.classList.add('show');
      boton.setAttribute('data-install-ready', deferredPrompt ? '1' : '0');
    };

    window.addEventListener('vextra:install-ready', mostrarSiDisponible);
    window.addEventListener('beforeinstallprompt', mostrarSiDisponible);
    mostrarSiDisponible();

    window.addEventListener('appinstalled', () => {
      deferredPrompt = null;
      window.__vextraDeferredInstallPrompt = null;
      if (botonInstalar) botonInstalar.classList.remove('show');
    });
  }

  prepararInstalacionPwa();
})();
