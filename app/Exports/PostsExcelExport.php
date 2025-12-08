<?php

namespace App\Exports;

use App\Models\Post;
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

class PostsExcelExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $ids;
    protected $dataCount;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function array(): array
    {
        $query = Post::select('id', 'title', 'status', 'visibility', 'views', 'allow_comments', 'created_at');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        $posts = $query->get()->map(function ($post) {
            return [
                $post->id,
                $post->title,
                match ($post->status) {
                    'draft' => 'Borrador',
                    'pending' => 'Pendiente',
                    'published' => 'Publicado',
                    'rejected' => 'Rechazado',
                    default => ucfirst($post->status),
                },
                match ($post->visibility) {
                    'public' => 'Público',
                    'private' => 'Privado',
                    'registered' => 'Registrado',
                    default => ucfirst($post->visibility),
                },
                $post->views,
                $post->allow_comments ? 'Sí' : 'No',
                $post->created_at ? $post->created_at->format('d/m/Y H:i') : '—',
            ];
        })->toArray();

        $this->dataCount = count($posts);

        $title = empty($this->ids) ? 'LISTA COMPLETA DE POSTS' : 'POSTS SELECCIONADOS';

        $result = [
            [$title, '', '', '', '', '', ''],
            ['ID', 'Título', 'Estado', 'Visibilidad', 'Vistas', 'Comentarios', 'Creado'],
        ];

        foreach ($posts as $postRow) {
            $result[] = $postRow;
        }

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF2563EB']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

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
            'B' => 35,
            'C' => 18,
            'D' => 18,
            'E' => 12,
            'F' => 16,
            'G' => 20,
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
                $sheet->mergeCells("A{$summaryRow}:G{$summaryRow}");
                $sheet->getStyle("A{$summaryRow}")->applyFromArray([
                    'font' => ['italic' => true, 'bold' => true, 'color' => ['argb' => 'FF374151']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                if ($dataEndRow >= $dataStartRow) {
                    $sheet->getStyle("A2:G{$dataEndRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new Color('FFCBD5E1'));
                }

                foreach (['A','B','C','D','E','F','G'] as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $width = $sheet->getColumnDimension($col)->getWidth();
                    if ($width > 50) {
                        $sheet->getColumnDimension($col)->setWidth(50);
                    }
                }

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

                $sheet->getParent()->setActiveSheetIndex(0);
                $sheet->setSelectedCell('A3');
            },
        ];
    }
}
