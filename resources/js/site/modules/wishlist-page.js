// Manejo de eliminación de productos en la lista de deseos sin recargar la página

function handleWishlistDelete(event, page, csrfToken, { updateHeaderAndEmptyState } = {}) {
	const form = event.target;
	if (!form.classList.contains('card-delete-btn')) return;

	event.preventDefault();

	if (!form.action) return;

	const article = form.closest('.wishlist-item');
	const layout = page.querySelector('.wishlist-layout');
	const headerCount = page.querySelector('.wishlist-count');
	const submitButton = form.querySelector('button[type="submit"]');

	if (submitButton) {
		submitButton.disabled = true;
	}

	(async () => {
		try {
			const formData = new FormData(form);

			const response = await fetch(form.action, {
				method: 'POST',
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'Accept': 'application/json',
					...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
				},
				body: formData,
			});

			const data = await response.json().catch(() => null);

			if (!response.ok || !data || data.ok === false) {
				if (data && data.toast && typeof window.showToast === 'function') {
					window.showToast(data.toast);
				} else if (typeof window.showToast === 'function') {
					window.showToast({
						type: 'danger',
						title: 'Error al eliminar',
						message: 'No se pudo eliminar el producto de favoritos.',
					});
				}
				return;
			}

			// Éxito: mostrar toast, actualizar UI
			if (data.toast && typeof window.showToast === 'function') {
				window.showToast(data.toast);
			}

			if (article) {
				article.remove();
			}

			if (updateHeaderAndEmptyState && typeof data.remaining_count === 'number') {
				const remaining = data.remaining_count;

				if (headerCount) {
					if (remaining > 0) {
						headerCount.textContent = `${remaining} producto${remaining === 1 ? '' : 's'}`;
					} else {
						headerCount.remove();
					}
				}

				// Si ya no quedan elementos, mostrar estado vacío (solo en página principal de wishlist)
				if (remaining === 0 && layout) {
					layout.remove();

					const empty = document.createElement('div');
					empty.className = 'card-empty';
					empty.innerHTML = `
						<div class="wishlist-empty-icon">
							<i class="ri-heart-fill"></i>
						</div>
						<h2 class="card-title">No tienes productos en tu lista de deseos</h2>
						<p>Explora el catálogo y guarda tus productos favoritos para verlos aquí.</p>
						<a href="/" class="boton-form boton-success py-3 px-5">
							<span class="boton-form-icon"><i class="ri-store-2-fill"></i></span>
							<span class="boton-form-text">Ir a la tienda</span>
						</a>
					`;

					const header = page.querySelector('.wishlist-header');
					if (header && header.parentNode) {
						header.parentNode.insertBefore(empty, header.nextSibling);
					} else {
						page.appendChild(empty);
					}
				}
			}
		} catch (error) {
			if (typeof window.showToast === 'function') {
				window.showToast({
					type: 'danger',
					title: 'Error inesperado',
					message: 'Ocurrió un problema al eliminar el producto. Inténtalo de nuevo.',
				});
			}
		} finally {
			if (submitButton) {
				submitButton.disabled = false;
			}
		}
	})();
}

function initWishlistPage() {
	const page = document.querySelector('.wishlist-page');
	if (!page) return;

	const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
	const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

	page.addEventListener('submit', (event) => {
		handleWishlistDelete(event, page, csrfToken, { updateHeaderAndEmptyState: true });
	});
}

function initProfileWishlistSection() {
	// Secciones de favoritos dentro de "Mi cuenta"
	const sections = document.querySelectorAll('.profile-section');
	if (!sections.length) return;

	const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
	const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

	sections.forEach((section) => {
		// Solo enganchar si dentro hay items de wishlist
		if (!section.querySelector('.wishlist-item')) return;

		section.addEventListener('submit', (event) => {
			handleWishlistDelete(event, section, csrfToken, { updateHeaderAndEmptyState: false });
		});
	});
}

function initWishlistInteractions() {
	initWishlistPage();
	initProfileWishlistSection();
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initWishlistInteractions);
} else {
	initWishlistInteractions();
}
