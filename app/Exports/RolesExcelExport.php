<?php

namespace App\Exports;

use App\Models\Role;
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

class RolesExcelExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $ids;
    protected $dataCount;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function array(): array
    {
        $query = Role::select('id', 'name', 'description', 'created_at');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        $roles = $query->get()->map(function ($role) {
            return [
                $role->id,
                $role->name,
                $role->description ?? 'Sin descripción',
                $role->created_at
                    ? $role->created_at->format('d/m/Y H:i')
                    : '—',
            ];
        })->toArray();

        $this->dataCount = count($roles);

        $title = empty($this->ids)
            ? 'LISTA COMPLETA DE ROLES'
            : 'ROLES SELECCIONADOS';

        $result = [
            [$title, '', '', ''],
            ['ID', 'Nombre', 'Descripción', 'Fecha de creación'],
        ];

        foreach ($roles as $role) {
            $result[] = $role;
        }

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF2563EB']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->getStyle('A2:D2')->applyFromArray([
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
            'C' => 60,
            'D' => 25,
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
                $sheet->mergeCells("A{$summaryRow}:D{$summaryRow}");
                $sheet->getStyle("A{$summaryRow}")->applyFromArray([
                    'font' => ['italic' => true, 'bold' => true, 'color' => ['argb' => 'FF374151']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                if ($dataEndRow >= $dataStartRow) {
                    $sheet->getStyle("A2:D{$dataEndRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new Color('FFCBD5E1'));
                }

                foreach (['A', 'B', 'D'] as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $width = $sheet->getColumnDimension($col)->getWidth();
                    if ($width > 50) {
                        $sheet->getColumnDimension($col)->setWidth(50);
                    }
                }

                $sheet->getColumnDimension('C')->setWidth(60);
                if ($dataEndRow >= $dataStartRow) {
                    $sheet->getStyle("C{$dataStartRow}:C{$dataEndRow}")
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(Alignment::VERTICAL_TOP);
                }

                for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                    if (($row - $dataStartRow) % 2 == 1) {
                        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
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
