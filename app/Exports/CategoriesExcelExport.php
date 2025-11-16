<?php

namespace App\Exports;

use App\Models\Category;
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

class CategoriesExcelExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $ids;
    protected $dataCount;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function array(): array
    {
        $query = Category::with(['family', 'parent'])
            ->select(
                'id',
                'name',
                'description',
                'status',
                'family_id',
                'parent_id',
                'created_at'
            );

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        $categories = $query->get()->map(function ($cat) {
            return [
                $cat->id,
                $cat->name,
                $cat->description,
                $cat->status == 1 ? 'Activo' : 'Inactivo',
                $cat->family ? $cat->family->name : '—',
                $cat->parent ? $cat->parent->name : '—',
                $cat->created_at
                    ? $cat->created_at->format('d/m/Y H:i')
                    : '—',
            ];
        })->toArray();

        $this->dataCount = count($categories);

        // Título
        $title = empty($this->ids)
            ? 'LISTA COMPLETA DE CATEGORÍAS'
            : 'CATEGORÍAS SELECCIONADAS';

        // Estructura:
        // 1 = título
        // 2 = cabeceras
        $result = [
            [$title, '', '', '', '', '', ''],
            ['ID', 'Nombre', 'Descripción', 'Estado', 'Familia', 'Categoría Padre', 'Fecha de creación'],
        ];

        foreach ($categories as $row) {
            $result[] = $row;
        }

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        // TÍTULO
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF2563EB']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // CABECERAS
        $sheet->getStyle('A2:G2')->applyFromArray([
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
            'C' => 60, // Descripción
            'D' => 15,
            'E' => 25,
            'F' => 25,
            'G' => 25,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $dataStartRow = 3;
                $dataEndRow = $sheet->getHighestRow();
                $summaryRow = $dataEndRow + 1;

                // TOTAL DE REGISTROS
                $sheet->setCellValue("A{$summaryRow}", "Total de registros: {$this->dataCount}");
                $sheet->mergeCells("A{$summaryRow}:G{$summaryRow}");
                $sheet->getStyle("A{$summaryRow}")->applyFromArray([
                    'font' => ['italic' => true, 'bold' => true, 'color' => ['argb' => 'FF374151']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                // BORDES DE TABLA
                if ($dataEndRow >= $dataStartRow) {
                    $sheet->getStyle("A2:G{$dataEndRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new Color('FFCBD5E1'));
                }

                // AUTOAJUSTE INTELIGENTE
                foreach (['A', 'B', 'D', 'E', 'F', 'G'] as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $width = $sheet->getColumnDimension($col)->getWidth();
                    if ($width > 50) {
                        $sheet->getColumnDimension($col)->setWidth(50);
                    }
                }

                // DESCRIPCIÓN: WRAP + ANCHO FIJO
                $sheet->getColumnDimension('C')->setWidth(60);
                if ($dataEndRow >= $dataStartRow) {
                    $sheet->getStyle("C{$dataStartRow}:C{$dataEndRow}")
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(Alignment::VERTICAL_TOP);
                }

                // FILAS ALTERNADAS
                for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                    if (($row - $dataStartRow) % 2 == 1) {
                        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF9FAFB'],
                            ],
                        ]);
                    }
                }

                // SELECCIONAR PRIMERA CELDA DE DATOS
                $sheet->getParent()->setActiveSheetIndex(0);
                $sheet->setSelectedCell('A3');
            },
        ];
    }
}
