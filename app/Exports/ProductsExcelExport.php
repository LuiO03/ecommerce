<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExcelExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $ids;
    protected $dataCount = 0;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function array(): array
    {
        $query = Product::with(['category:id,name'])
            ->withCount(['variants'])
            ->select('id', 'sku', 'name', 'price', 'discount', 'status', 'category_id', 'created_at');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        $rows = $query->get()->map(function ($product) {
            return [
                $product->id,
                $product->sku,
                $product->name,
                optional($product->category)->name ?? '—',
                number_format((float) $product->price, 2),
                $product->discount !== null
                    ? number_format((float) $product->discount, 2)
                    : '—',
                $product->status ? 'Activo' : 'Inactivo',
                $product->variants_count,
                optional($product->created_at)?->format('d/m/Y H:i') ?? '—',
            ];
        })->toArray();

        $this->dataCount = count($rows);

        $title = empty($this->ids)
            ? 'LISTA COMPLETA DE PRODUCTOS'
            : 'PRODUCTOS SELECCIONADOS';

        $data = [
            [$title, '', '', '', '', '', '', '', ''],
            ['ID', 'SKU', 'Nombre', 'Categoría', 'Precio', 'Descuento', 'Estado', 'Variantes', 'Fecha de creación'],
        ];

        foreach ($rows as $row) {
            $data[] = $row;
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF8B5CF6']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->getStyle('A2:I2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFEDE9FE'],
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(25);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 18,
            'C' => 28,
            'D' => 26,
            'E' => 14,
            'F' => 14,
            'G' => 14,
            'H' => 12,
            'I' => 20,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $dataStart = 3;
                $lastRow = $sheet->getHighestRow();

                if ($lastRow >= $dataStart) {
                    $sheet->getStyle("A2:I{$lastRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new Color('FFD1D5DB'));

                    $sheet->getStyle("A{$dataStart}:I{$lastRow}")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    for ($row = $dataStart; $row <= $lastRow; $row++) {
                        if (($row - $dataStart) % 2 === 1) {
                            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['argb' => 'FFF8FAFC'],
                                ],
                            ]);
                        }
                    }
                }

                $summaryRow = $lastRow + 1;
                $sheet->setCellValue("A{$summaryRow}", "Total de registros: {$this->dataCount}");
                $sheet->mergeCells("A{$summaryRow}:I{$summaryRow}");
                $sheet->getStyle("A{$summaryRow}")->applyFromArray([
                    'font' => ['italic' => true, 'bold' => true, 'color' => ['argb' => 'FF4B5563']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                $sheet->setSelectedCell('A3');
            },
        ];
    }
}
