<?php

namespace App\Exports;

use App\Models\Order;
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

class OrdersExcelExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $ids;
    protected $dataCount;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function array(): array
    {
        $query = Order::with('user')
            ->select('id', 'order_number', 'user_id', 'total', 'status', 'payment_status', 'payment_id', 'created_at');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        $orders = $query->get()->map(function ($order) {
            return [
                $order->id,
                $order->order_number,
                optional($order->user)->name ?? '—',
                number_format((float) $order->total, 2),
                $order->status,
                $order->payment_status,
                $order->payment_id ?? '—',
                $order->created_at
                    ? $order->created_at->format('d/m/Y H:i')
                    : '—',
            ];
        })->toArray();

        $this->dataCount = count($orders);

        $title = empty($this->ids)
            ? 'LISTA COMPLETA DE ORDENES'
            : 'ORDENES SELECCIONADAS';

        $result = [
            [$title, '', '', '', '', '', '', ''],
            ['ID', 'N° Orden', 'Cliente', 'Total', 'Estado', 'Pago', 'ID Pago', 'Fecha de creación'],
        ];

        foreach ($orders as $order) {
            $result[] = $order;
        }

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF2563EB']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->getStyle('A2:H2')->applyFromArray([
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
            'B' => 18,
            'C' => 30,
            'D' => 15,
            'E' => 15,
            'F' => 18,
            'G' => 22,
            'H' => 22,
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

                $sheet->setCellValue("A{$summaryRow}", "Total de registros: {$this->dataCount}");
                $sheet->mergeCells("A{$summaryRow}:H{$summaryRow}");
                $sheet->getStyle("A{$summaryRow}")->applyFromArray([
                    'font' => ['italic' => true, 'bold' => true, 'color' => ['argb' => 'FF374151']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                if ($dataEndRow >= $dataStartRow) {
                    $sheet->getStyle("A2:H{$dataEndRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new Color('FFCBD5E1'));
                }

                foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'] as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $width = $sheet->getColumnDimension($col)->getWidth();
                    if ($width > 50) {
                        $sheet->getColumnDimension($col)->setWidth(50);
                    }
                }

                for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                    if (($row - $dataStartRow) % 2 == 1) {
                        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF9FAFB'],
                            ],
                        ]);
                    }
                }

                $sheet->getParent()->setActiveSheetIndex(0);
                $sheet->setSelectedCell('A3');
            },
        ];
    }
}
