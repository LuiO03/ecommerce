const initSearchAutocomplete = () => {
    const forms = document.querySelectorAll('[data-search-form]');

    forms.forEach((form) => {
        const input = form.querySelector('[data-search-input]');
        const dropdown = form.querySelector('[data-search-dropdown]');
        const resultsList = form.querySelector('[data-search-results]');

        if (!input || !dropdown || !resultsList) {
            return;
        }

        let timeoutId;

        const clearResults = () => {
            resultsList.innerHTML = '';
            dropdown.classList.remove('is-open');
        };

        const renderSection = (title, items, formatter) => {
            if (!items.length) {
                return '';
            }

            const listItems = items.map(formatter).join('');
            return `
                <div class="search-suggestions-section">
                    <div class="search-suggestions-title">${title}</div>
                    ${listItems}
                </div>
            `;
        };

        const fetchSuggestions = async (value) => {
            const params = new URLSearchParams({ q: value });
            const url = `${form.dataset.searchSuggestions}?${params.toString()}`;

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                return { products: [], categories: [] };
            }

            return response.json();
        };

        input.addEventListener('input', () => {
            const value = input.value.trim();

            if (timeoutId) {
                clearTimeout(timeoutId);
            }

            if (value.length < 2) {
                clearResults();
                return;
            }

            timeoutId = setTimeout(async () => {
                const data = await fetchSuggestions(value);

                const productSection = renderSection('Productos', data.products || [], (item) => {
                    return `
                        <a class="search-suggestions-item" href="/products/${item.slug}">
                            <span class="search-suggestions-name">${item.name}</span>
                            <span class="search-suggestions-meta">S/.${Number(item.price).toFixed(2)}</span>
                        </a>
                    `;
                });

                const categorySection = renderSection('Categorias', data.categories || [], (item) => {
                    return `
                        <a class="search-suggestions-item" href="/categories/${item.slug}">
                            <span class="search-suggestions-name">${item.name}</span>
                            <span class="search-suggestions-meta">Categoria</span>
                        </a>
                    `;
                });

                const combined = `${productSection}${categorySection}`.trim();

                if (!combined) {
                    clearResults();
                    return;
                }

                resultsList.innerHTML = combined;
                dropdown.classList.add('is-open');
            }, 250);
        });

        input.addEventListener('focus', () => {
            if (resultsList.innerHTML.trim() !== '') {
                dropdown.classList.add('is-open');
            }
        });

        document.addEventListener('click', (event) => {
            if (!form.contains(event.target)) {
                clearResults();
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', initSearchAutocomplete);
