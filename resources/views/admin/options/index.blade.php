@section('title', 'Opciones y valores')

@php
    use App\Models\Option;
    use Illuminate\Support\Str;
@endphp

<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-info"><i class="ri-settings-3-line"></i></div>
        Opciones y Valores
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
                    @php
                        $isColorOption = $option->isColor();
                    @endphp
                    <article class="option-card ripple-card" data-option-inline="true"
                        data-option-slug="{{ $option->slug }}"
                        data-option-is-color="{{ $isColorOption ? 'true' : 'false' }}"
                        data-create-url="{{ route('admin.options.features.store', $option) }}">
                        <header class="option-card-header">
                            <div class="card-header">
                                <span class="card-title">{{ $option->name }}</span>
                                <p class="card-description">
                                    {{ $option->description }}
                                </p>
                            </div>
                            <div class="option-card-actions">
                                <a href="{{ route('admin.options.edit', $option) }}" class="boton-form boton-warning"
                                    title="Editar">
                                    <i class="ri-edit-circle-fill"></i>
                                </a>
                                <button type="button" class="boton-form boton-danger" data-action="delete-option"
                                    data-action-url="{{ route('admin.options.destroy', $option) }}"
                                    data-name="{{ $option->name }}" title="Eliminar">
                                    <i class="ri-delete-bin-2-fill"></i>
                                </button>
                            </div>
                        </header>

                        <div class="option-feature-pills" data-role="feature-list">
                            @forelse ($option->features as $feature)
                                @php
                                    $isColor = $option->isColor();
                                    $displayValue = $feature->value;
                                    $colorPreview =
                                        $isColor && preg_match('/^#([0-9A-F]{3}|[0-9A-F]{6})$/i', $displayValue)
                                            ? strtoupper($displayValue)
                                            : null;
                                @endphp
                                <div class="option-feature-pill {{ $colorPreview ? 'is-color' : '' }}"
                                    data-feature-id="{{ $feature->id }}"
                                    data-delete-url="{{ route('admin.options.features.destroy', [$option, $feature]) }}">
                                    @if ($colorPreview)
                                        <span class="pill-color" style="--pill-color: {{ $colorPreview }}"></span>
                                    @endif
                                    <span class="pill-value">{{ $displayValue }}</span>
                                    @if ($feature->description)
                                        <span class="pill-description">{{ $feature->description }}</span>
                                    @endif
                                    <button type="button" class="pill-remove" data-action="feature-remove"
                                        aria-label="Eliminar valor">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            @empty
                                <p class="option-feature-empty" data-role="feature-empty">Sin valores registrados.</p>
                            @endforelse
                        </div>

                        <div class="option-feature-inline">
                            <form id="featureForm-{{ $option->id }}" class="option-feature-form"
                                data-role="feature-form" data-option-is-color="{{ $isColorOption ? 'true' : 'false' }}"
                                data-create-url="{{ route('admin.options.features.store', $option) }}" novalidate>
                                <div class="option-feature-fields">
                                    @if ($isColorOption)
                                        <div class="input-group">
                                            <div class="input-icon-container">
                                                <i class="ri-palette-line input-icon"></i>
                                                <input type="text" id="feature-value-{{ $option->id }}"
                                                    data-role="feature-value" name="feature_value" placeholder="#RRGGBB"
                                                    style="cursor: pointer" autocomplete="off" data-role="feature-value"
                                                    data-validate="required|colorCss" data-coloris>
                                            </div>
                                            <script>
                                                Coloris({
                                                    theme: 'pill',
                                                    themeMode: 'dark',
                                                    swatches: [
                                                        'DarkSlateGray',
                                                        '#2a9d8f',
                                                        '#e9c46a',
                                                        'coral',
                                                        'rgb(231, 111, 81)',
                                                        'Crimson',
                                                        '#023e8a',
                                                        '#0077b6',
                                                        'hsl(194, 100%, 39%)',
                                                        '#00b4d8',
                                                        '#48cae4'
                                                    ],
                                                    onChange: (color, inputEl) => {
                                                        console.log(`The new color is ${color}`);
                                                    }
                                                });
                                            </script>
                                        </div>
                                        <div class="input-group">
                                            <div class="input-icon-container">
                                                <i class="ri-align-left input-icon"></i>
                                                <input type="text" id="feature-description-{{ $option->id }}"
                                                    data-role="feature-description" name="feature_description"
                                                    placeholder="Nombre del color" maxlength="255" autocomplete="off"
                                                    data-validate="required|max:50|min:3" required>
                                            </div>
                                        </div>
                                    @else
                                        <div class="input-group">
                                            <div class="input-icon-container">
                                                <i class="ri-price-tag-3-line input-icon"></i>
                                                <input type="text" id="feature-value-{{ $option->id }}"
                                                    data-role="feature-value" name="feature_value"
                                                    placeholder="Nuevo valor" maxlength="120" autocomplete="off"
                                                    data-validate="required|max:120" required>
                                            </div>
                                        </div>
                                        <div class="input-group">
                                            <div class="input-icon-container">
                                                <i class="ri-align-left input-icon"></i>
                                                <input type="text" id="feature-description-{{ $option->id }}"
                                                    data-role="feature-description" name="feature_description"
                                                    placeholder="Descripción (opcional)" maxlength="255"
                                                    autocomplete="off" data-validate="max:255">
                                            </div>
                                        </div>
                                    @endif

                                    <div class="input-group">
                                        <button type="submit" id="featureSubmit-{{ $option->id }}"
                                            class="boton-form boton-success" data-role="feature-submit"
                                            title="Agregar valor">
                                            <i class="ri-add-large-line"></i>
                                        </button>
                                    </div>
                                </div>
                                <span class="input-error-message" data-role="feature-feedback" aria-live="polite"
                                    style="display: none;">
                                    <i class="ri-error-warning-line"></i>
                                    <span class="error-text" data-role="feature-feedback-text"></span>
                                </span>
                            </form>
                        </div>

                        <footer class="option-card-footer">
                            <span>
                                <i class="ri-price-tag-2-fill"></i>
                                <span data-role="feature-count" data-label-singular="valor"
                                    data-label-plural="valores" data-count="{{ $option->features->count() }}">
                                    {{ $option->features->count() }}
                                    {{ Str::plural('valores', $option->features->count()) }}
                                </span>
                            </span>
                            <span data-role="updated-wrapper">
                                <i class="ri-time-fill"></i>
                                <span data-role="updated-text">
                                    Actualizado {{ optional($option->updated_at)->diffForHumans() ?? 'sin fecha' }}
                                </span>
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
                        card.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        setTimeout(() => card.classList.remove('is-highlighted'), 3200);
                    }
                }

                if (typeof window.initOptionInlineManager === 'function') {
                    window.initOptionInlineManager();
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
