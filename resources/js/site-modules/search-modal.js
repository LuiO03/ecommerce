// search-modal.js - Control de la modal de búsqueda global

function getSearchModal() {
	return document.getElementById('siteSearchModal');
}

function openSearchModal() {
	const modal = getSearchModal();
	if (!modal) return;

	modal.classList.add('is-visible');
	modal.setAttribute('aria-hidden', 'false');

	// Enfocar el input de búsqueda al abrir
	const input = modal.querySelector('[data-search-input]');
	if (input) {
		setTimeout(() => {
			input.focus();
		}, 50);
	}
}

function closeSearchModal() {
	const modal = getSearchModal();
	if (!modal) return;

	modal.classList.remove('is-visible');
	modal.setAttribute('aria-hidden', 'true');
}

function setupSearchModal() {
	const modal = getSearchModal();
	if (!modal) return;

	// Botones que abren la modal
	document.querySelectorAll('[data-site-search-open]').forEach((trigger) => {
		trigger.addEventListener('click', (event) => {
			if (event) event.preventDefault();
			openSearchModal();
		});
	});

	// Elementos que cierran la modal
	modal.querySelectorAll('[data-site-search-close]').forEach((el) => {
		el.addEventListener('click', (event) => {
			if (event) event.preventDefault();
			closeSearchModal();
		});
	});

	// Cerrar con ESC
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			closeSearchModal();
		}
	});

	// Evitar enviar la búsqueda si el término está vacío
	const form = modal.querySelector('[data-search-form]');
	if (form) {
		const input = form.querySelector('[data-search-input]');
		form.addEventListener('submit', (event) => {
			if (!input) return;
			const term = input.value.trim();
			if (!term) {
				event.preventDefault();
				input.focus();
			}
		});
	}
}

document.addEventListener('DOMContentLoaded', setupSearchModal);
