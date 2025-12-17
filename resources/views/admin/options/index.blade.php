@php
    use App\Models\Option;
    use Illuminate\Support\Str;
@endphp

<x-admin-layout :showMobileFab="true" :useSlotContainer="false">
    <x-slot name="title">
        <div class="page-icon card-info"><i class="ri-settings-3-line"></i></div>
        Opciones y valores
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.options.create') }}" class="boton boton-primary">
            <span class="boton-icon"><i class="ri-add-circle-fill"></i></span>
            <span class="boton-text">Crear opción</span>
        </a>
    </x-slot>

    <section class="options-wrapper">
        @if ($options->isEmpty())
            <div class="options-empty">
                <div class="options-empty-icon"><i class="ri-compass-3-line"></i></div>
                <h3>No hay opciones registradas</h3>
                <p>Crea tu primera opción para gestionar atributos como talla, color o materiales.</p>
                <a href="{{ route('admin.options.create') }}" class="boton boton-primary">
                    <span class="boton-icon"><i class="ri-add-circle-fill"></i></span>
                    <span class="boton-text">Crear opción</span>
                </a>
            </div>
        @else
            <div class="options-grid">
                @foreach ($options as $option)
                    <article class="option-card" data-option-slug="{{ $option->slug }}">
                        <header class="option-card-header">
                            <div>
                                <h3>{{ $option->name }}</h3>
                                <span class="option-type-badge">
                                    <i class="ri-shapes-line"></i>
                                    {{ $typeLabels[$option->type] ?? 'Sin tipo' }}
                                </span>
                            </div>
                            <div class="option-card-actions">
                                <a href="{{ route('admin.options.edit', $option) }}" class="boton-sm boton-warning" title="Editar">
                                    <span class="boton-sm-icon"><i class="ri-edit-circle-fill"></i></span>
                                </a>
                                <button type="button"
                                        class="boton-sm boton-danger"
                                        data-action="delete-option"
                                        data-action-url="{{ route('admin.options.destroy', $option) }}"
                                        data-name="{{ $option->name }}"
                                        title="Eliminar">
                                    <span class="boton-sm-icon"><i class="ri-delete-bin-2-fill"></i></span>
                                </button>
                            </div>
                        </header>

                        @if ($option->description)
                            <p class="option-card-description">{{ $option->description }}</p>
                        @endif

                        <div class="option-feature-pills">
                            @forelse ($option->features as $feature)
                                @php
                                    $isColor = (int) $option->type === Option::TYPE_COLOR;
                                    $displayValue = $feature->value;
                                    $colorPreview = $isColor && preg_match('/^#([0-9A-F]{3}|[0-9A-F]{6})$/i', $displayValue)
                                        ? strtoupper($displayValue)
                                        : null;
                                @endphp
                                <div class="option-feature-pill {{ $colorPreview ? 'is-color' : '' }}">
                                    @if ($colorPreview)
                                        <span class="pill-color" style="--pill-color: {{ $colorPreview }}"></span>
                                    @endif
                                    <span class="pill-value">{{ $displayValue }}</span>
                                    @if ($feature->description)
                                        <span class="pill-description">{{ $feature->description }}</span>
                                    @endif
                                </div>
                            @empty
                                <p class="option-feature-empty">Sin valores registrados.</p>
                            @endforelse
                        </div>

                        <footer class="option-card-footer">
                            <span>
                                <i class="ri-price-tag-2-line"></i>
                                {{ $option->features->count() }} {{ Str::plural('valores', $option->features->count()) }}
                            </span>
                            <span>
                                <i class="ri-time-line"></i>
                                Actualizado {{ optional($option->updated_at)->diffForHumans() ?? 'sin fecha' }}
                            </span>
                        </footer>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <form method="POST" id="deleteOptionForm" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const highlightSlug = @json(session('highlightOption'));
                if (highlightSlug) {
                    const card = document.querySelector(`[data-option-slug="${highlightSlug}"]`);
                    if (card) {
                        card.classList.add('is-highlighted');
                        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        setTimeout(() => card.classList.remove('is-highlighted'), 3200);
                    }
                }

                const deleteForm = document.getElementById('deleteOptionForm');

                document.querySelectorAll('[data-action="delete-option"]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const name = button.dataset.name;
                        const actionUrl = button.dataset.actionUrl;

                        if (typeof window.showConfirm !== 'function') {
                            if (confirm(`¿Eliminar la opción ${name}?`)) {
                                deleteForm.setAttribute('action', actionUrl);
                                deleteForm.submit();
                            }
                            return;
                        }

                        window.showConfirm({
                            type: 'danger',
                            header: 'Eliminar opción',
                            title: '¿Deseas continuar?',
                            message: `Esta acción eliminará la opción <strong>${name}</strong> y sus valores asociados.`,
                            confirmText: 'Sí, eliminar',
                            cancelText: 'Cancelar',
                            onConfirm: () => {
                                deleteForm.setAttribute('action', actionUrl);
                                deleteForm.submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
</x-admin-layout>
