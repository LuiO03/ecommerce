<div id="modalShowProduct" class="modal-show hidden modal-horizontal">
	<div class="modal-content">
		<div class="modal-show-header">
			<h6>Detalles del Producto</h6>
			<button type="button" id="closeProductModal" class="confirm-close ripple-btn">
				<i class="ri-close-line"></i>
			</button>
		</div>

		<h3 class="modal-title-body" id="product-name-title">—</h3>
		<div class="modal-show-body">
			<div class="modal-show-row">
				<div class="modal-img-container">
                    <div id="product-main-image">-</div>
                    <div class="modal-show-actions">
                        <a href="#" id="modalProductEditBtn" class="boton boton-warning" title="Editar producto">
                            <span class="boton-icon"><i class="ri-edit-circle-fill"></i></span>
                            <span class="boton-text">Editar</span>
                        </a>

                        <form id="modalProductDeleteForm" action="#" method="POST" title="Eliminar producto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="boton boton-danger">
                                <span class="boton-icon"><i class="ri-delete-bin-2-fill"></i></span>
                                <span class="boton-text">Eliminar</span>
                            </button>
                        </form>
                    </div>
                </div>


				<table class="modal-show-table">
					<tr>
						<th>ID</th>
						<td id="product-id">—</td>
					</tr>
					<tr>
						<th>Slug</th>
						<td id="product-slug">—</td>
					</tr>
					<tr>
						<th>SKU</th>
						<td id="product-sku">—</td>
					</tr>
					<tr>
						<th>Nombre</th>
						<td id="product-name">—</td>
					</tr>
					<tr>
						<th>Categoría</th>
						<td id="product-category">—</td>
					</tr>
					<tr>
						<th>Precio</th>
						<td id="product-price">—</td>
					</tr>
					<tr>
						<th>Descuento</th>
						<td id="product-discount">—</td>
					</tr>
					<tr>
						<th>Estado</th>
						<td id="product-status">—</td>
					</tr>
					<tr>
						<th>Variantes</th>
						<td id="product-variants">—</td>
					</tr>
					<tr>
						<th>Creado por</th>
						<td id="product-created-by">—</td>
					</tr>
					<tr>
						<th>Actualizado por</th>
						<td id="product-updated-by">—</td>
					</tr>
					<tr>
						<th>Descripción</th>
						<td id="product-description">—</td>
					</tr>
				</table>
			</div>

			<div class="modal-gallery" id="product-gallery"></div>
		</div>

		<div class="modal-show-footer">
			<button type="button" class="boton boton-modal-close" id="cancelProductModal" title="Cerrar ventana">
				<span class="boton-icon text-base"><i class="ri-close-line"></i></span>
				<span class="boton-text">Cerrar</span>
			</button>
		</div>
	</div>
</div>

@push('scripts')
	<script>
		function openProductModal() {
			$('#modalShowProduct').removeClass('hidden');
			$('#modalShowProduct .modal-content').removeClass('animate-out').addClass('animate-in');
			$('#modalShowProduct').appendTo('body');
			document.addEventListener('keydown', escProductListener);
			document.addEventListener('mousedown', clickOutsideProductListener);
		}

		function closeProductModal() {
			const $content = $('#modalShowProduct .modal-content');
			$content.removeClass('animate-in').addClass('animate-out');
			setTimeout(() => {
				$('#modalShowProduct').addClass('hidden');
				setLoadingProductFields();
				document.removeEventListener('keydown', escProductListener);
				document.removeEventListener('mousedown', clickOutsideProductListener);
			}, 250);
		}

		function setLoadingProductFields() {
			$('#product-name-title').html('<div class="shimmer shimmer-cell shimmer-title" style="width: 220px;"></div>');
			$('#product-id').html('<div class="shimmer shimmer-cell" style="width: 40px;"></div>');
			$('#product-slug').html('<div class="shimmer shimmer-cell" style="width: 120px;"></div>');
			$('#product-sku').html('<div class="shimmer shimmer-cell" style="width: 100px;"></div>');
			$('#product-name').html('<div class="shimmer shimmer-cell" style="width: 180px;"></div>');
			$('#product-category').html('<div class="shimmer shimmer-cell" style="width: 140px;"></div>');
			$('#product-price').html('<div class="shimmer shimmer-cell" style="width: 80px;"></div>');
			$('#product-discount').html('<div class="shimmer shimmer-cell" style="width: 60px;"></div>');
			$('#product-status').html('<div class="shimmer shimmer-cell" style="width: 70px;"></div>');
			$('#product-variants').html('<div class="shimmer shimmer-cell" style="width: 90px;"></div>');
			$('#product-created-by').html('<div class="shimmer shimmer-cell" style="width: 180px;"></div>');
			$('#product-updated-by').html('<div class="shimmer shimmer-cell" style="width: 180px;"></div>');
			$('#product-description').html('<div class="shimmer shimmer-cell" style="width: 240px;"></div>');
			$('#product-gallery').html('<div class="shimmer shimmer-img" style="width:100%;height:120px;"></div>');
			$('#product-main-image').html('<div class="shimmer shimmer-img"></div>');
		}

		function renderProductModal(data) {
			$('#product-id').text(data.id ?? '—');
			$('#product-slug').html(`<span class="badge badge-primary slug-mono">${data.slug}</span>`);
			$('#product-sku').html(`<span class="badge badge-gray">${data.sku}</span>`);
			$('#product-name-title').text(data.name ?? '—');
			$('#product-name').text(data.name ?? '—');

			if (data.category) {
				$('#product-category').html(`
					<span class="badge badge-info">
						<i class="ri-price-tag-3-line"></i>
						${data.category.name}
					</span>
				`);
			} else {
				$('#product-category').html('<span class="badge badge-gray">Sin categoría</span>');
			}

			$('#product-price').html(`
				<span class="badge badge-success-light">
					<i class="ri-currency-line"></i>
					${Number(data.price ?? 0).toFixed(2)}
				</span>
			`);

			if (data.discount && Number(data.discount) > 0) {
				$('#product-discount').html(`
					<span class="badge badge-warning-light">
						<i class="ri-discount-percent-line"></i>
						${Number(data.discount).toFixed(2)}
					</span>
				`);
			} else {
				$('#product-discount').html('<span class="text-muted-td">Sin descuento</span>');
			}

			if (data.status) {
				$('#product-status').html('<span class="badge boton-success"><i class="ri-checkbox-circle-fill"></i> Activo</span>');
			} else {
				$('#product-status').html('<span class="badge boton-danger"><i class="ri-close-circle-fill"></i> Inactivo</span>');
			}

			const variantsCount = data.variants ? data.variants.length : 0;
			if (variantsCount > 0) {
				const variantsList = data.variants.map(variant => `
					<li>
						<span class="badge badge-secondary-light">
							<i class="ri-hashtag"></i> ${variant.sku}
						</span>
						<span class="badge badge-success-light">
							<i class="ri-currency-line"></i> ${Number(variant.price ?? 0).toFixed(2)}
						</span>
						<span class="badge badge-gray">
							<i class="ri-archive-stack-line"></i> Stock ${variant.stock ?? 0}
						</span>
					</li>
				`).join('');

				$('#product-variants').html(`<ul class="modal-list">${variantsList}</ul>`);
			} else {
				$('#product-variants').html('<span class="text-muted-td">Sin variantes</span>');
			}

			$('#product-created-by').html(`
				<div class="show-cell-content">
					<span class="font-bold">${data.created_by_name}</span>
					<span class="show-date"><i class="ri-time-fill"></i> ${data.created_at}</span>
				</div>
			`);

			$('#product-updated-by').html(`
				<div class="show-cell-content">
					<span class="font-bold">${data.updated_by_name}</span>
					<span class="show-date"><i class="ri-time-fill"></i> ${data.updated_at}</span>
				</div>
			`);

			$('#product-description').text(data.description ?? 'Sin descripción');

			const mainImage = data.images?.find((image) => image.is_main && image.url);
			if (mainImage) {
				$('#product-main-image').html(`
					<img src="${mainImage.url}" alt="${data.name}" class="modal-img">
				`);
			} else {
				$('#product-main-image').html(`
					<div class="modal-img-placeholder">
						<i class="ri-image-add-fill"></i>
						Sin imagen principal
					</div>
				`);
			}

			const galleryImages = (data.images || []).filter(image => !image.is_main && image.url);
			if (galleryImages.length) {
				const galleryHtml = galleryImages.map(image => `
					<div class="modal-gallery-item">
						<img src="${image.url}" alt="${data.name}" />
						<span class="gallery-order">#${image.order}</span>
					</div>
				`).join('');
				$('#product-gallery').html(galleryHtml);
			} else {
				$('#product-gallery').html('<p class="label-hint"><i class="ri-information-line"></i> No hay imágenes adicionales.</p>');
			}

			$('#modalProductEditBtn').attr('href', `/admin/products/${data.slug}/edit`);
			$('#modalProductDeleteForm').attr('action', `/admin/products/${data.slug}`);
		}

		function loadProductModal(slug) {
			setLoadingProductFields();
			openProductModal();
			$.ajax({
				url: `/admin/products/${slug}/show`,
				method: 'GET',
				success: function(response) {
					renderProductModal(response);
				},
				error: function() {
					$('#product-name-title').text('Error al cargar');
					$('#product-description').text('No se pudo obtener la información del producto.');
				}
			});
		}

		$(document).on('click', '.btn-ver-producto', function() {
			const slug = $(this).data('slug');
			loadProductModal(slug);
		});

		$('#closeProductModal, #cancelProductModal').on('click', closeProductModal);

		function escProductListener(event) {
			if (event.key === 'Escape') {
				closeProductModal();
			}
		}

		function clickOutsideProductListener(event) {
			const overlay = document.getElementById('modalShowProduct');
			const content = document.querySelector('#modalShowProduct .modal-content');
			if (event.target === overlay && !content.contains(event.target)) {
				closeProductModal();
			}
		}

		$(document).on('submit', '#modalProductDeleteForm', function(event) {
			event.preventDefault();
			const form = this;

			document.removeEventListener('mousedown', clickOutsideProductListener);

			window.showConfirm({
				type: 'danger',
				header: 'Eliminar producto',
				title: '¿Deseas continuar?',
				message: 'Esta acción es irreversible.<br>Se eliminará el producto <strong>' + $('#product-name').text() + '</strong>.',
				confirmText: 'Sí, eliminar',
				cancelText: 'No, cancelar',
				onConfirm: function() {
					document.addEventListener('mousedown', clickOutsideProductListener);
					form.submit();
				},
				onCancel: function() {
					document.addEventListener('mousedown', clickOutsideProductListener);
				}
			});
		});
	</script>
@endpush
