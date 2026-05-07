// auth-wishlist-modal.js - Control de la modal de autenticación para wishlist

function getModal() {
	return document.getElementById('authWishlistModal');
}

function openModal() {
	const modal = getModal();
	if (!modal) return;

	modal.classList.add('is-visible');
	modal.setAttribute('aria-hidden', 'false');
}

function closeModal() {
	const modal = getModal();
	if (!modal) return;

	modal.classList.remove('is-visible');
	modal.setAttribute('aria-hidden', 'true');
}

function setupAuthWishlistModal() {
	const modal = getModal();
	if (!modal) return;

	// Cerrar por botones marcados
	modal.querySelectorAll('[data-auth-wishlist-close]').forEach((el) => {
		el.addEventListener('click', (event) => {
			if (event) event.preventDefault();
			closeModal();
		});
	});

	// Cerrar con ESC
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			closeModal();
		}
	});

	// Exponer helper global
	window.showAuthWishlistModal = openModal;
}

// Integración con Livewire: escuchar evento para abrir la modal

document.addEventListener('livewire:init', () => {
	if (typeof window.Livewire === 'undefined') {
		return;
	}

	setupAuthWishlistModal();

	window.Livewire.on('show-auth-wishlist-modal', () => {
		openModal();
	});
});
