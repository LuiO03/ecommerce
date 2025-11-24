<div class="flex flex-col gap-2 items-center">
    <a href="{{ route('admin.profile.export.excel') }}" class="boton boton-primary w-full">
        <i class="ri-file-excel-2-line"></i> Descargar Excel
    </a>
    <a href="{{ route('admin.profile.export.pdf') }}" class="boton boton-danger w-full">
        <i class="ri-file-pdf-2-line"></i> Descargar PDF
    </a>
    <a href="{{ route('admin.profile.export.csv') }}" class="boton boton-success w-full">
        <i class="ri-file-csv-line"></i> Descargar CSV
    </a>
</div>
