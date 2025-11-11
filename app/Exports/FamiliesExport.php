<?php

namespace App\Exports;

use App\Models\Family;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class FamiliesExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $ids;
    protected $dataCount;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function array(): array
    {
        $query = Family::select('id', 'name', 'description', 'status', 'created_at');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        $families = $query->get()->map(function ($family) {
            return [
                $family->id,
                $family->name,
                $family->description,
                $family->status == 1 ? 'Activo' : 'Inactivo',
                $family->created_at
                    ? $family->created_at->format('d/m/Y H:i')
                    : '—',
            ];
        })->toArray();

        $this->dataCount = count($families);

        // Determinar título
        $title = empty($this->ids)
            ? 'LISTA COMPLETA DE FAMILIAS'
            : 'FAMILIAS SELECCIONADAS';

        // Estructura del archivo:
        // Fila 1: Título
        // Fila 2: Cabeceras (sin separador)
        // Fila 3+: Datos
        $result = [
            // Fila 1: Título centrado
            [$title, '', '', '', ''],
            // Fila 2: Cabeceras (directamente después del título)
            ['ID', 'Nombre', 'Descripción', 'Estado', 'Fecha de creación'],
        ];

        // Agregar los datos de las familias
        foreach ($families as $family) {
            $result[] = $family;
        }

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        // 🔹 Título (fila 1) - ya está en los datos, solo aplicar estilos
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF2563EB']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // 🔹 Cabeceras (fila 2) - mejorar estilos
        $sheet->getStyle('A2:E2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE5E7EB'],
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(25);
    }


    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 25,
            'C' => 60, // 🔹 ancho máximo fijo para descripción
            'D' => 15,
            'E' => 25,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $dataStartRow = 3; // Los datos empiezan en fila 3 (título=1, cabeceras=2)
                $dataEndRow = $sheet->getHighestRow();
                $summaryRow = $dataEndRow + 1; // Total inmediatamente después de los datos

                // 🔹 Agregar número total de registros
                $sheet->setCellValue("A{$summaryRow}", "Total de registros: {$this->dataCount}");
                $sheet->mergeCells("A{$summaryRow}:E{$summaryRow}");
                $sheet->getStyle("A{$summaryRow}")->applyFromArray([
                    'font' => ['italic' => true, 'bold' => true, 'color' => ['argb' => 'FF374151']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                // 🔹 Bordes solo a la tabla de datos (sin incluir título)
                if ($dataEndRow >= $dataStartRow) {
                    $sheet->getStyle("A2:E{$dataEndRow}") // Desde cabeceras hasta último dato
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new Color('FFCBD5E1'));
                }

                // 🔹 Autoajuste inteligente excepto descripción
                foreach (['A', 'B', 'D', 'E'] as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $width = $sheet->getColumnDimension($col)->getWidth();
                    if ($width > 50) {
                        $sheet->getColumnDimension($col)->setWidth(50);
                    }
                }

                // 🔹 Mantener ancho fijo y ajustar texto en descripción
                $sheet->getColumnDimension('C')->setWidth(60);
                if ($dataEndRow >= $dataStartRow) {
                    $sheet->getStyle("C{$dataStartRow}:C{$dataEndRow}")
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(Alignment::VERTICAL_TOP);
                }

                // 🔹 Aplicar estilos alternados a las filas de datos
                for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                    if (($row - $dataStartRow) % 2 == 1) { // Filas impares (respecto a los datos)
                        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF9FAFB'],
                            ],
                        ]);
                    }
                }

                // 🔹 Seleccionar la primera celda con datos
                $sheet->getParent()->setActiveSheetIndex(0);
                $sheet->setSelectedCell('A3'); // Primera fila de datos
            },
        ];
    }
}
