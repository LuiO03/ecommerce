@php
    use App\Models\Option;
@endphp

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-warning"><i class="ri-edit-circle-line"></i></div>
        <div class="page-edit-title">
            <span class="page-subtitle">Editar Opción</span>
            {{ $option->name }}
        </div>
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.options.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
    </x-slot>

    <form action="{{ route('admin.options.update', $option) }}" method="POST" class="form-container" id="optionForm" autocomplete="off">
        @csrf
        @method('PUT')

        @include('admin.options.partials.form', ['option' => $option])

        <div class="form-footer">
            <a href="{{ route('admin.options.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
                <span class="boton-form-text">Cancelar</span>
            </a>
            <button type="submit" class="boton-form boton-success" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-fill"></i></span>
                <span class="boton-form-text">Actualizar opción</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                initOptionFeatureForm({
                    containerId: 'featureList',
                    addButtonId: 'addFeatureBtn',
                    templateId: 'featureRowTemplate',
                    nameInputId: 'name'
                });

                initSubmitLoader({
                    formId: 'optionForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
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
