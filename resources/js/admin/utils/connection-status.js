export function initConnectionStatusBar() {
  const bar = document.getElementById('connectionStatusBar');
  if (!bar) return;

  const textEl = document.getElementById('connectionStatusText');
  const iconEl = document.getElementById('connectionStatusIcon');

  let hideTimeout = null;

  const setState = (isOnline, { show = true, autoHide = false } = {}) => {
    if (!bar || !textEl || !iconEl) return;

    if (hideTimeout) {
      clearTimeout(hideTimeout);
      hideTimeout = null;
    }

    if (!show) {
      bar.classList.remove('connection-visible');
      return;
    }

    bar.classList.add('connection-visible');

    if (isOnline) {
      bar.classList.remove('connection-offline');
      bar.classList.add('connection-online');
      textEl.textContent = 'Conexión a Internet restablecida';
      iconEl.className = 'ri-wifi-line';

      if (autoHide) {
        hideTimeout = setTimeout(() => {
          bar.classList.remove('connection-visible');
        }, 2500);
      }
    } else {
      bar.classList.remove('connection-online');
      bar.classList.add('connection-offline');
      textEl.textContent = 'Sin conexión a Internet. Intentando reconectar...';
      iconEl.className = 'ri-wifi-off-line';
    }
  };

  // Estado inicial
  if (typeof navigator !== 'undefined' && 'onLine' in navigator) {
    const isOnline = navigator.onLine;
    if (!isOnline) {
      setState(false, { show: true });
    }
  }

  window.addEventListener('online', () => {
    setState(true, { show: true, autoHide: true });
  });

  window.addEventListener('offline', () => {
    setState(false, { show: true });
  });
}
