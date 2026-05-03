<?php

namespace App\Exports;

use App\Models\Family;
use Illuminate\Support\Facades\Auth;
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

class FamiliesExcelExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $ids;
    protected $dataCount;
    protected $totalCategories;
    protected $totalProducts;
    protected $meta;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function array(): array
    {
        $query = Family::query()
            ->select(['id', 'name', 'slug', 'status'])
            ->withCount('categories')
            ->withCount('products');

        $isSelectedExport = !empty($this->ids);

        if ($isSelectedExport) {
            $query->whereIn('id', $this->ids);
        }

        $items = $query->orderByDesc('id')->get();

        $this->dataCount = $items->count();
        $this->totalCategories = (int) $items->sum('categories_count');
        $this->totalProducts = (int) $items->sum('products_count');

        $companySettings = function_exists('company_setting') ? company_setting() : null;
        $companyName = !empty($companySettings?->name)
            ? $companySettings->name
            : config('app.name');

        $exportType = $isSelectedExport
            ? 'Exportación seleccionada'
            : 'Exportación total';

        $generatedAt = now()->format('d/m/Y H:i');
        $userName = Auth::user()->name ?? 'Administrador';

        $this->meta = [
            'companyName' => $companyName,
            'exportType' => $exportType,
            'generatedAt' => $generatedAt,
            'userName' => $userName,
        ];

        $notice = $isSelectedExport
            ? 'Este archivo contiene únicamente familias seleccionadas por el usuario.'
            : 'Este archivo contiene el listado completo de familias registradas.';

        $result = [];

        // Encabezado estilo reporte (similar al PDF)
        $result[] = [$companyName, '', '', '', '', ''];
        $result[] = ['Panel administrativo', '', '', '', '', ''];
        $result[] = ['Reporte de Familias', '', '', '', '', ''];
        $result[] = ["{$exportType} | Emitido: {$generatedAt} | Usuario: {$userName}", '', '', '', '', ''];
        $result[] = [$notice, '', '', '', '', ''];

        // Resumen (3 tarjetas)
        $result[] = [$isSelectedExport ? 'Seleccionadas' : 'Total familias', '', 'Categorías', '', 'Productos', ''];
        $result[] = [$this->dataCount, '', $this->totalCategories, '', $this->totalProducts, ''];

        // Espacio
        $result[] = ['', '', '', '', '', ''];

        // Cabeceras de tabla
        $result[] = ['ID', 'Familia', 'Slug', 'Categorías', 'Productos', 'Estado'];

        foreach ($items as $family) {
            $result[] = [
                $family->id,
                $family->name,
                $family->slug ?: '—',
                (int) $family->categories_count,
                (int) $family->products_count,
                $family->status ? 'Activo' : 'Inactivo',
            ];
        }

        // Fila total
        $result[] = ["Total de registros: {$this->dataCount}", '', '', '', '', ''];

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        // El grueso del layout se aplica en AfterSheet.
        // Aquí solo dejamos la fuente base.
        $sheet->getStyle('A:Z')->getFont()->setName('Calibri')->setSize(11);
    }


    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 28,
            'C' => 28,
            'D' => 12,
            'E' => 12,
            'F' => 14,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Estructura fija
                $headerEndRow = 5;
                $summaryLabelRow = 6;
                $summaryValueRow = 7;
                $tableHeaderRow = 9;
                $dataStartRow = 10;
                $dataEndRow = $dataStartRow + max(0, (int) $this->dataCount - 1);
                $totalRow = $dataEndRow + 1;

                // Merges principales
                foreach ([1, 2, 3, 4, 5] as $r) {
                    $sheet->mergeCells("A{$r}:F{$r}");
                }
                $sheet->mergeCells('A6:B6');
                $sheet->mergeCells('C6:D6');
                $sheet->mergeCells('E6:F6');
                $sheet->mergeCells('A7:B7');
                $sheet->mergeCells('C7:D7');
                $sheet->mergeCells('E7:F7');
                $sheet->mergeCells("A{$totalRow}:F{$totalRow}");

                // Alturas
                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(3)->setRowHeight(24);
                $sheet->getRowDimension(5)->setRowHeight(28);
                $sheet->getRowDimension($tableHeaderRow)->setRowHeight(20);

                // Header styles
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF111827']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['argb' => 'FF6B7280']],
                ]);
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => 'FF111827']],
                ]);
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['argb' => 'FF6B7280']],
                ]);
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['argb' => 'FF111827']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFEFF6FF'],
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFDBEAFE'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                // Summary cards
                $sheet->getStyle('A6:F6')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF6B7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF9FAFB'],
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFE5E7EB'],
                        ],
                    ],
                ]);
                $sheet->getStyle('A7:F7')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF111827']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF9FAFB'],
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFE5E7EB'],
                        ],
                    ],
                ]);

                // Table header
                $sheet->getStyle("A{$tableHeaderRow}:F{$tableHeaderRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF111827']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFEEF2FF'],
                    ],
                ]);

                // Bordes tabla
                if ($this->dataCount > 0) {
                    $sheet->getStyle("A{$tableHeaderRow}:F{$dataEndRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new Color('FFE5E7EB'));

                    // Alternar filas
                    for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                        if (($row - $dataStartRow) % 2 === 1) {
                            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['argb' => 'FFFAFAFA'],
                                ],
                            ]);
                        }
                    }

                    // Alineaciones por columnas
                    $sheet->getStyle("A{$dataStartRow}:A{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$dataStartRow}:E{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$dataStartRow}:F{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Colores estado
                    for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                        $status = (string) $sheet->getCell("F{$row}")->getValue();
                        $color = $status === 'Activo' ? 'FF15803D' : 'FFB91C1C';
                        $sheet->getStyle("F{$row}")->getFont()->setBold(true)->getColor()->setARGB($color);
                    }
                }

                // Total row
                $sheet->getStyle("A{$totalRow}")->applyFromArray([
                    'font' => ['italic' => true, 'bold' => true, 'color' => ['argb' => 'FF374151']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                // Freeze panes (mantener cabecera de tabla)
                $sheet->freezePane("A{$dataStartRow}");

                // Seleccionar primera celda útil
                $sheet->getParent()->setActiveSheetIndex(0);
                $sheet->setSelectedCell('A10');
            },
        ];
    }
}
