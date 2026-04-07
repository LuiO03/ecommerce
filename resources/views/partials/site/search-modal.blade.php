<div id="siteSearchModal" class="site-search-modal" aria-hidden="true">
    <div class="site-search-backdrop" data-site-search-close></div>

    <div class="site-search-dialog" role="dialog" aria-modal="true" aria-labelledby="siteSearchModalTitle">
        <button type="button" class="site-search-close" data-site-search-close aria-label="Cerrar">
            <i class="ri-close-line"></i>
        </button>

        <div class="card-header">
            <span class="card-title">Buscar productos</span>
            <p class="card-description">
                Escribe el nombre de un producto o categoría para encontrar lo que necesitas.
            </p>
        </div>

        <form class="site-search-modal-form" role="search" aria-label="Buscar productos"
            action="{{ route('search.index') }}" method="GET" data-search-form
            data-search-suggestions="{{ route('search.suggestions') }}">
            <div class="site-search-input-wrapper">
                <i class="ri-search-2-line site-search-input-icon" aria-hidden="true"></i>
                <input type="search" name="q" class="site-search-input" data-search-input
                    placeholder="¿Qué producto estás buscando?" autocomplete="off" />
                <button type="submit" class="site-search-btn" aria-label="Buscar">
                    <span>Ver todo</span>
                </button>
            </div>

            <div class="search-suggestions" data-search-dropdown>
                <div class="search-suggestions-results" data-search-results></div>
            </div>
        </form>
    </div>
</div>
