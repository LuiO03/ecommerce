<div class="form-profile-column">
    <div class="flex items-center gap-2 mb-4">
        <div class="page-icon card-success"><i class="ri-download-2-line"></i></div>
        <h2 class="font-bold text-lg">Exportar perfil</h2>
    </div>
    <p class="mb-4 text-muted">Exporta tu información de perfil en el formato que prefieras.</p>
    <x-alert type="success" title="¿Privacidad?" :dismissible="true">
        Solo tú puedes exportar tu información. Los datos se descargan en tu dispositivo.
    </x-alert>
    <div class="form-footer">
        <a href="{{ route('admin.profile.export.excel') }}" class="boton boton-primary w-full">
            <span class="boton-icon"><i class="ri-file-excel-2-line"></i></span>
            <span class="boton-text">Descargar Excel</span>
        </a>
        <a href="{{ route('admin.profile.export.pdf') }}" class="boton boton-danger w-full">
            <span class="boton-icon"><i class="ri-file-pdf-2-line"></i></span>
            <span class="boton-text">Descargar PDF</span>
        </a>
        <a href="{{ route('admin.profile.export.csv') }}" class="boton boton-success w-full">
            <span class="boton-icon"><i class="ri-file-csv-line"></i></span>
            <span class="boton-text">Descargar CSV</span>
        </a>
    </div>
</div>
