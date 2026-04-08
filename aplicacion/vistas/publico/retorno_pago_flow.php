<section class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-7">
        <div class="card shadow-sm border-0">
          <div class="card-body p-4 p-md-5 text-center">
            <div id="flow-retorno-loader" class="mb-4">
              <div class="spinner-border text-primary mb-3" role="status" style="width:3rem;height:3rem;">
                <span class="visually-hidden">Cargando...</span>
              </div>
              <h2 class="h4 mb-2">Estamos validando tu pago</h2>
              <p class="text-muted mb-0">Este proceso puede tardar unos segundos. No cierres esta ventana.</p>
            </div>

            <div id="flow-retorno-resultado" class="d-none">
              <div id="flow-retorno-icono" class="display-5 mb-3">✅</div>
              <h2 id="flow-retorno-titulo" class="h4 mb-2"></h2>
              <p id="flow-retorno-mensaje" class="text-muted mb-4"></p>
            </div>

            <div class="d-flex justify-content-center gap-2 flex-wrap">
              <a id="flow-btn-login" href="<?= e(url('/iniciar-sesion')) ?>" class="btn btn-primary px-4">
                Iniciar sesión
              </a>
              <a href="<?= e(url('/planes')) ?>" class="btn btn-outline-secondary px-4">
                Ver planes
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  (() => {
    const endpoint = <?= json_encode(url('/flow/retorno/pago/estado'), JSON_UNESCAPED_SLASHES) ?>;
    const urlNoConfirmado = <?= json_encode(url('/flow/retorno/pago/no-confirmado'), JSON_UNESCAPED_SLASHES) ?>;
    const token = <?= json_encode((string) ($token ?? ''), JSON_UNESCAPED_SLASHES) ?>;
    const origen = <?= json_encode((string) ($origen ?? 'registro'), JSON_UNESCAPED_SLASHES) ?>;
    const suscripcionId = <?= json_encode((int) ($suscripcion_id ?? 0), JSON_UNESCAPED_SLASHES) ?>;

    const loader = document.getElementById('flow-retorno-loader');
    const resultado = document.getElementById('flow-retorno-resultado');
    const titulo = document.getElementById('flow-retorno-titulo');
    const mensaje = document.getElementById('flow-retorno-mensaje');
    const icono = document.getElementById('flow-retorno-icono');
    const btnLogin = document.getElementById('flow-btn-login');

    const render = (data) => {
      if (data?.estado && data.estado !== 'aprobado') {
        window.location.href = `${urlNoConfirmado}?estado=${encodeURIComponent(data.estado)}`;
        return;
      }

      const tipo = data?.tipo || 'warning';
      const mapIcon = { success: '✅', danger: '❌', warning: '⏳' };
      icono.textContent = mapIcon[tipo] || 'ℹ️';
      titulo.textContent = data?.titulo || 'Estado de pago';
      mensaje.textContent = data?.mensaje || 'No fue posible obtener el estado del pago.';
      if (data?.login_url) {
        btnLogin.href = data.login_url;
      }

      loader.classList.add('d-none');
      resultado.classList.remove('d-none');
    };

    const body = new URLSearchParams();
    body.set('token', token);
    body.set('origen', origen);
    body.set('suscripcion_id', String(suscripcionId || 0));

    fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body: body.toString(),
    })
      .then((resp) => resp.json())
      .then((data) => render(data))
      .catch(() => {
        render({
          tipo: 'warning',
          titulo: 'Estamos procesando tu pago',
          mensaje: 'No pudimos confirmar el estado ahora mismo. Puedes iniciar sesión y reintentar en unos segundos.',
          login_url: <?= json_encode(url('/iniciar-sesion'), JSON_UNESCAPED_SLASHES) ?>,
        });
      });
  })();
</script>
