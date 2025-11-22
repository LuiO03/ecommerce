<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class UsersExcelExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $ids;
    protected $dataCount;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function array(): array
    {
        $query = User::select('id', 'name', 'last_name', 'email', 'dni', 'phone', 'status', 'created_at');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        $users = $query->get()->map(function ($user) {
            return [
                $user->id,
                $user->name,
                $user->last_name ?? '—',
                $user->email,
                $user->dni ?? '—',
                $user->phone ?? '—',
                $user->status == 1 ? 'Activo' : 'Inactivo',
                $user->created_at
                    ? $user->created_at->format('d/m/Y H:i')
                    : '—',
            ];
        })->toArray();

        $this->dataCount = count($users);

        $title = empty($this->ids)
            ? 'LISTA COMPLETA DE USUARIOS'
            : 'USUARIOS SELECCIONADOS';

        $result = [
            [$title, '', '', '', '', '', '', ''],
            ['ID', 'Nombre', 'Apellido', 'Email', 'DNI', 'Teléfono', 'Estado', 'Fecha de creación'],
        ];

        foreach ($users as $user) {
            $result[] = $user;
        }

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        // Título (fila 1)
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF2563EB']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Cabeceras (fila 2)
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
            'A' => 8,   // ID
            'B' => 25,  // Nombre
            'C' => 25,  // Apellido
            'D' => 30,  // Email
            'E' => 12,  // DNI
            'F' => 15,  // Teléfono
            'G' => 12,  // Estado
            'H' => 20,  // Fecha
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastRow = $this->dataCount + 2;

                // Bordes para toda la tabla
                $event->sheet->getStyle("A2:H{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFD1D5DB'],
                        ],
                    ],
                ]);

                // Centrar ID y Estado
                $event->sheet->getStyle("A3:A{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle("G3:G{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Alternar colores de filas
                for ($row = 3; $row <= $lastRow; $row++) {
                    if ($row % 2 == 0) {
                        $event->sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF9FAFB'],
                            ],
                        ]);
                    }
                }

                // Ajustar altura de filas
                for ($row = 3; $row <= $lastRow; $row++) {
                    $event->sheet->getRowDimension($row)->setRowHeight(20);
                }
            },
        ];
    }
}
