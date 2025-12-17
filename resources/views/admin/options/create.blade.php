@php
    use App\Models\Option;
@endphp

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-primary"><i class="ri-slideshow-3-line"></i></div>
        Nueva opción
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.options.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
    </x-slot>

    <form action="{{ route('admin.options.store') }}" method="POST" class="form-container" id="optionForm" autocomplete="off">
        @csrf
        @include('admin.options.partials.form', ['option' => null, 'typeLabels' => $typeLabels])

        <div class="form-footer">
            <a href="{{ route('admin.options.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
                <span class="boton-form-text">Cancelar</span>
            </a>
            <button type="reset" class="boton-form boton-warning">
                <span class="boton-form-icon"><i class="ri-eraser-fill"></i></span>
                <span class="boton-form-text">Limpiar</span>
            </button>
            <button type="submit" class="boton-form boton-success" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-fill"></i></span>
                <span class="boton-form-text">Crear opción</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                initOptionFeatureForm({
                    containerId: 'featureList',
                    addButtonId: 'addFeatureBtn',
                    typeSelectId: 'type',
                    templateId: 'featureRowTemplate'
                });

                initSubmitLoader({
                    formId: 'optionForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Guardando...'
                });

                initFormValidator('#optionForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });
            });
        </script>
    @endpush
</x-admin-layout>
