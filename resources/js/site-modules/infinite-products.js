const setupInfiniteProducts = () => {
    const containers = document.querySelectorAll('[data-infinite-products]');

    containers.forEach((container) => {
        const button = container.querySelector('[data-load-more-button]');
        const sentinel = container.querySelector('[data-load-more-sentinel]');

        if (!button || !sentinel) {
            return;
        }

        if (button.dataset.observerAttached === 'true') {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    if (button.disabled) {
                        return;
                    }

                    button.click();
                });
            },
            { rootMargin: '200px' }
        );

        observer.observe(sentinel);
        button.dataset.observerAttached = 'true';
    });
};

document.addEventListener('DOMContentLoaded', setupInfiniteProducts);
document.addEventListener('livewire:load', setupInfiniteProducts);
document.addEventListener('livewire:update', setupInfiniteProducts);
document.addEventListener('livewire:navigated', setupInfiniteProducts);
