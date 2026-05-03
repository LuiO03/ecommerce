<?php

namespace App\Exports;

use App\Models\Category;
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

class CategoriesExcelExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $ids;
    protected $dataCount;
    protected $totalSubcategories;
    protected $totalProducts;
    protected $meta;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function array(): array
    {
        $query = Category::query()
            ->select(['id', 'name', 'slug', 'status', 'family_id', 'parent_id'])
            ->withCount('products')
            ->with([
                'family:id,name',
                'parent:id,name,family_id,parent_id',
                'parent.family:id,name',
            ]);

        $isSelectedExport = !empty($this->ids);

        if ($isSelectedExport) {
            $query->whereIn('id', $this->ids);
        }

        $items = $query->orderByDesc('id')->get();

        $this->dataCount = $items->count();
        $this->totalSubcategories = (int) $items->whereNotNull('parent_id')->count();
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
            ? 'Este archivo contiene únicamente categorías seleccionadas por el usuario.'
            : 'Este archivo contiene el listado completo de categorías registradas.';

        $result = [];

        $result[] = [$companyName, '', '', '', '', ''];
        $result[] = ['Panel administrativo', '', '', '', '', ''];
        $result[] = ['Reporte de Categorías', '', '', '', '', ''];
        $result[] = ["{$exportType} | Emitido: {$generatedAt} | Usuario: {$userName}", '', '', '', '', ''];
        $result[] = [$notice, '', '', '', '', ''];

        $result[] = [$isSelectedExport ? 'Seleccionadas' : 'Total categorías', '', 'Subcategorías', '', 'Productos', ''];
        $result[] = [$this->dataCount, '', $this->totalSubcategories, '', $this->totalProducts, ''];
        $result[] = ['', '', '', '', '', ''];

        $result[] = ['ID', 'Categoría', 'Slug', 'Ubicación', 'Productos', 'Estado'];

        foreach ($items as $cat) {
            $result[] = [
                $cat->id,
                $cat->name,
                $cat->slug ?: '—',
                $cat->location,
                (int) $cat->products_count,
                $cat->status ? 'Activo' : 'Inactivo',
            ];
        }

        $result[] = ["Total de registros: {$this->dataCount}", '', '', '', '', ''];

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:Z')->getFont()->setName('Calibri')->setSize(11);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 28,
            'C' => 28,
            'D' => 50,
            'E' => 12,
            'F' => 14,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $tableHeaderRow = 9;
                $dataStartRow = 10;
                $dataEndRow = $dataStartRow + max(0, (int) $this->dataCount - 1);
                $totalRow = $dataEndRow + 1;

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

                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(3)->setRowHeight(24);
                $sheet->getRowDimension(5)->setRowHeight(28);
                $sheet->getRowDimension($tableHeaderRow)->setRowHeight(20);

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

                $sheet->getStyle("A{$tableHeaderRow}:F{$tableHeaderRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF111827']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFEEF2FF'],
                    ],
                ]);

                if ($this->dataCount > 0) {
                    $sheet->getStyle("A{$tableHeaderRow}:F{$dataEndRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new Color('FFE5E7EB'));

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

                    $sheet->getStyle("A{$dataStartRow}:A{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$dataStartRow}:F{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$dataStartRow}:D{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                        $status = (string) $sheet->getCell("F{$row}")->getValue();
                        $color = $status === 'Activo' ? 'FF15803D' : 'FFB91C1C';
                        $sheet->getStyle("F{$row}")->getFont()->setBold(true)->getColor()->setARGB($color);
                    }

                    // Ubicación wrap
                    $sheet->getStyle("D{$dataStartRow}:D{$dataEndRow}")
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(Alignment::VERTICAL_TOP);
                }

                $sheet->getStyle("A{$totalRow}")->applyFromArray([
                    'font' => ['italic' => true, 'bold' => true, 'color' => ['argb' => 'FF374151']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                $sheet->freezePane("A{$dataStartRow}");
                $sheet->getParent()->setActiveSheetIndex(0);
                $sheet->setSelectedCell('A10');
            },
        ];
    }
}
