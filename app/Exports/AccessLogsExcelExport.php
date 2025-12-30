<?php

namespace App\Exports;

use App\Models\AccessLog;
use Illuminate\Contracts\Support\Responsable;
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

class AccessLogsExcelExport implements FromArray, WithStyles, WithColumnWidths, WithEvents, Responsable
{
    protected $ids;
    protected $dataCount;
    private $filename;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
        $this->filename = 'access_logs_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
    }

    public function array(): array
    {
        $query = AccessLog::with('user:id,name')
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderByDesc('created_at');

        $logs = $query->get()->map(function($log) {
            return [
                $log->id,
                $log->user ? $log->user->name : 'Invitado',
                $log->email,
                $log->action_label,
                $log->status_label,
                $log->ip_address,
                $log->created_at->format('d/m/Y H:i'),
                $log->user_agent,
            ];
        })->toArray();

        $this->dataCount = count($logs);

        $title = empty($this->ids)
            ? 'LISTA COMPLETA DE REGISTROS DE ACCESO'
            : 'REGISTROS DE ACCESO SELECCIONADOS';

        $result = [
            // Fila 1: Título
            [$title, '', '', '', '', '', '', ''],
            // Fila 2: Cabeceras
            ['ID', 'Usuario', 'Email', 'Acción', 'Estado', 'IP', 'Fecha', 'Agente'],
        ];
        foreach ($logs as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        // Título
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF2563EB']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Cabeceras
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
            'B' => 20,
            'C' => 30,
            'D' => 15,
            'E' => 15,
            'F' => 18,
            'G' => 20,
            'H' => 60,
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

                // Total de registros
                $sheet->setCellValue("A{$summaryRow}", "Total de registros: {$this->dataCount}");
                $sheet->mergeCells("A{$summaryRow}:H{$summaryRow}");
                $sheet->getStyle("A{$summaryRow}")->applyFromArray([
                    'font' => ['italic' => true, 'bold' => true, 'color' => ['argb' => 'FF374151']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                // Bordes
                if ($dataEndRow >= $dataStartRow) {
                    $sheet->getStyle("A2:H{$dataEndRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new Color('FFCBD5E1'));
                }

                // Autoajuste inteligente
                foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $width = $sheet->getColumnDimension($col)->getWidth();
                    if ($width > 50) {
                        $sheet->getColumnDimension($col)->setWidth(50);
                    }
                }
                $sheet->getColumnDimension('H')->setWidth(60);
                if ($dataEndRow >= $dataStartRow) {
                    $sheet->getStyle("H{$dataStartRow}:H{$dataEndRow}")
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(Alignment::VERTICAL_TOP);
                }

                // Alternancia de filas
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

    public function toResponse($request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download($this, $this->filename);
    }
}
