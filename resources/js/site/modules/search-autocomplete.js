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

        const renderSection = (title, items, formatter, sectionClass = '') => {
            if (!items.length) {
                return '';
            }

            const listItems = items.map(formatter).join('');
            return `
                <div class="search-suggestions-section ${sectionClass}">
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

                const products = data.products || [];
                const categories = data.categories || [];

                const productSection = renderSection('Productos', products, (item) => {
                        const basePrice = (item.discounted_price ?? item.price) || 0;
                        const currentPrice = Number(basePrice).toFixed(2);
                    const originalPrice = Number(item.price || 0).toFixed(2);

                    return `
                        <a class="search-suggestions-item search-suggestions-item-product" href="/products/${item.slug}">
                            <div class="search-suggestion-product-main">
                                <div class="search-suggestion-thumb">
                                    ${item.image_url
                                        ? `<img src="${item.image_url}" alt="${item.name}">`
                                        : '<i class="ri-image-line"></i>'}
                                </div>
                                <div class="search-suggestion-text">
                                    <span class="search-suggestions-name">${item.name}</span>
                                </div>
                            </div>
                            <div class="search-suggestion-price">
                                <span class="search-suggestion-price-current">S/.${currentPrice}</span>
                                ${item.has_discount
                                    ? `<span class="search-suggestion-price-original">S/.${originalPrice}</span>`
                                    : ''}
                            </div>
                        </a>
                    `;
                }, 'search-suggestions-section--products');

                const categorySection = renderSection('Categorías', categories, (item) => {
                    return `
                        <a class="search-suggestions-item" href="/categories/${item.slug}">
                            <span class="search-suggestions-name">${item.name}</span>
                        </a>
                    `;
                });

                const hasAny = products.length > 0 || categories.length > 0;
                const combined = `${productSection}${categorySection}`.trim();

                if (!hasAny || !combined) {
                    clearResults();
                    return;
                }

                let footer = '';
                if (hasAny && value.length >= 2) {
                    footer = `
                        <div class="search-suggestions-footer">
                            <button type="button" class="search-suggestions-see-all" data-search-see-all>
                                Ver todo
                            </button>
                        </div>
                    `;
                }

                resultsList.innerHTML = `${combined}${footer}`;
                dropdown.classList.add('is-open');

                const seeAllBtn = form.querySelector('[data-search-see-all]');
                if (seeAllBtn) {
                    seeAllBtn.onclick = () => {
                        const term = input.value.trim();
                        if (!term) return;
                        const params = new URLSearchParams({ q: term });
                        window.location.href = `${form.action}?${params.toString()}`;
                    };
                }
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
