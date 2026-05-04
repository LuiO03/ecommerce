<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Exports\AuditsExcelExport;
use App\Exports\AuditsCsvExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuditController extends Controller
{
    public function __construct()
    {
        // Permisos para ver y exportar auditorías
        $this->middleware('can:auditorias.index')->only(['index', 'show', 'data']);
        $this->middleware('can:auditorias.export')->only(['exportExcel', 'exportPdf', 'exportCsv']);
    }

    /**
     * Listado de auditorías del sistema.
     *
     * Referencia: estructura simple de index() en FamilyController,
     * pero aplicada al modelo Audit.
     */
    public function index(Request $request)
    {
        return view('admin.audits.index');
    }

    /**
     * Devuelve los datos completos de una auditoría en formato JSON.
     */
    public function show(Audit $audit)
    {
        $audit->load('user');

        return response()->json([
            'id'                  => $audit->id,
            'event'               => $audit->event,
            'event_label'         => $audit->event_label,
            'description'         => $audit->description,
            'auditable_type'      => $audit->auditable_type,
            'auditable_type_name' => $audit->model_name,
            'auditable_id'        => $audit->auditable_id,
            'old_values'          => $audit->old_values,
            'new_values'          => $audit->new_values,
            'ip_address'          => $audit->ip_address,
            'user_agent'          => $audit->user_agent,
            'created_at'          => optional($audit->created_at)->format('d/m/Y H:i:s'),
            'user_name'           => optional($audit->user)->name,
            'user_email'          => optional($audit->user)->email,
        ]);
    }

    /**
     * Endpoint server-side para DataTables.
     *
     * Soporta:
     * - Paginación (start/length)
     * - Búsqueda global (search[value])
     * - Ordenamiento básico (order[0][column]/order[0][dir])
     * - Filtros extra enviados por DataTableManager en d.filters
     *   (ej.: filters[eventFilter] = created|updated|deleted)
     */
    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $filters = (array) $request->input('filters', []);
        $eventFilter = isset($filters['eventFilter']) ? trim((string) $filters['eventFilter']) : '';

        $recordsTotal = Audit::query()->count();

        $query = Audit::query()->with('user');

        if ($eventFilter !== '') {
            $query->where('event', $eventFilter);
        }

        if ($searchValue !== '') {
            $query->where(function ($q) use ($searchValue) {
                $like = '%' . $searchValue . '%';

                $q->where('event', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('auditable_type', 'like', $like)
                    ->orWhere('ip_address', 'like', $like)
                    ->orWhereHas('user', function ($uq) use ($like) {
                        $uq->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    });

                if (ctype_digit($searchValue)) {
                    $q->orWhere('id', (int) $searchValue)
                        ->orWhere('auditable_id', (int) $searchValue);
                }
            });
        }

        $recordsFiltered = (clone $query)->count();

        // Ordenamiento (mapeo por índice de columna en la tabla de Auditorías)
        $orderColumnIndex = (int) $request->input('order.0.column', 2);
        $orderDir = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $orderableMap = [
            2 => 'id',
            5 => 'event',
            7 => 'ip_address',
            8 => 'created_at',
        ];

        // Columna "Modelo" (índice 4) NO existe como columna en BD.
        // Es un accessor (model_name), así que ordenamos por auditable_type + auditable_id.
        if ($orderColumnIndex === 4) {
            $query->orderBy('auditable_type', $orderDir)
                ->orderBy('auditable_id', $orderDir);
        } else {
            $orderBy = $orderableMap[$orderColumnIndex] ?? 'id';
            $query->orderBy($orderBy, $orderDir);
        }

        if ($length !== -1) {
            $query->skip($start)->take(max(1, $length));
        }

        $audits = $query->get();

        $eventMeta = [
            'created' => ['badge-success', 'ri-add-circle-fill', 'Creado'],
            'updated' => ['badge-warning', 'ri-pencil-fill', 'Actualizado'],
            'deleted' => ['badge-danger', 'ri-delete-bin-fill', 'Eliminado'],
            'status_updated' => ['badge-primary', 'ri-refresh-fill', 'Estado Actualizado'],
            'bulk_deleted' => ['badge-danger', 'ri-delete-bin-2-fill', 'Eliminación Múltiple'],
            'pdf_exported' => ['badge-pink', 'ri-file-download-fill', 'PDF Exportado'],
            'excel_exported' => ['badge-success', 'ri-file-download-fill', 'Excel Exportado'],
            'csv_exported' => ['badge-orange', 'ri-file-download-fill', 'CSV Exportado'],
            'post_approved' => ['badge-success', 'ri-checkbox-circle-fill', 'Post Aprobado'],
            'post_rejected' => ['badge-danger', 'ri-close-circle-fill', 'Post Rechazado'],
            'permissions_updated' => ['badge-primary', 'ri-shield-check-fill', 'Permisos Actualizados'],
            'profile_updated' => ['badge-gray', 'ri-user-settings-fill', 'Perfil Actualizado'],
            'company_main_updated' => ['badge-gray', 'ri-building-4-fill', 'Negocio Actualizado'],
            'company_social_updated' => ['badge-gray', 'ri-share-fill', 'Redes del Negocio'],
            'company_legal_updated' => ['badge-gray', 'ri-file-law-fill', 'Legal del Negocio'],
        ];

        $data = $audits->map(function (Audit $audit) use ($eventMeta) {
            $modelName = $audit->model_name ?? ($audit->auditable_type ? class_basename($audit->auditable_type) : '—');
            $modelId = $audit->auditable_id ?? '—';

            $userCell = '';
            if ($audit->user) {
                $userCell = '<span class="badge badge-primary"><i class="ri-user-3-fill"></i> ' . e($audit->user->name) . '</span>';
            } else {
                $userCell = '<span class="badge badge-gray"><i class="ri-shield-keyhole-fill"></i> Sistema / Invitado</span>';
            }

            $event = (string) $audit->event;
            $eventLabel = ucfirst($event);
            if (isset($eventMeta[$event])) {
                [$badgeClass, $icon, $label] = $eventMeta[$event];
                $eventCell = '<span class="badge ' . $badgeClass . '"><i class="' . $icon . '"></i> ' . e($label) . '</span>';
            } else {
                $eventCell = '<span class="badge badge-secondary"><i class="ri-question-fill"></i> ' . e($eventLabel) . '</span>';
            }

            $actions = '<button class="boton-show-actions"><i class="ri-more-fill"></i></button>'
                . '<div class="tabla-botones">'
                . '<button class="boton-sm boton-info btn-ver-audit" data-id="' . e((string) $audit->id) . '" title="Ver cambios">'
                . '<i class="ri-eye-2-fill"></i>'
                . '<span class="boton-sm-text">Ver cambios</span>'
                . '</button>'
                . '</div>';

            return [
                '',
                '<div><input type="checkbox" class="check-row" name="audits[]" value="' . e((string) $audit->id) . '"></div>',
                (string) $audit->id,
                $userCell,
                e($modelName) . ' &middot; #' . e((string) $modelId),
                '<span data-event="' . e($event) . '">' . $eventCell . '</span>',
                e((string) $audit->description),
                '<code>' . e((string) ($audit->ip_address ?? '—')) . '</code>',
                e(optional($audit->created_at)->format('d/m/Y H:i')),
                $actions,
            ];
        })->values();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        $filename = 'auditorias_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'excel_exported',
            'auditable_type' => Audit::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'ids'        => $ids,
                'export_all' => $request->boolean('export_all', false),
                'filename'   => $filename,
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        return Excel::download(new AuditsExcelExport($ids), $filename);
    }

    public function exportPdf(Request $request)
    {
        $query = Audit::query()
            ->select([
                'id',
                'user_id',
                'event',
                'auditable_type',
                'auditable_id',
                'ip_address',
                'user_agent',
                'created_at',
            ])
            ->with('user:id,name,last_name,email');

        $isSelectedExport = false;

        if ($request->filled('ids')) {
            $query->whereIn('id', (array) $request->ids);
            $isSelectedExport = true;
        } elseif ($request->boolean('export_all')) {
            // exportación total
        } else {
            Session::flash('info', [
                'type'    => 'danger',
                'header'  => 'Error',
                'title'   => 'Sin selección',
                'message' => 'No se seleccionaron auditorías para exportar.',
            ]);

            return back();
        }

        $audits = $query->orderByDesc('id')->get();

        if ($audits->isEmpty()) {
            Session::flash('info', [
                'type'    => 'danger',
                'header'  => 'Error',
                'title'   => 'Sin datos',
                'message' => 'No hay auditorías disponibles para exportar.',
            ]);

            return back();
        }

        $filename = 'auditorias_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        /*
        |--------------------------------------------------------------------------
        | Registrar exportación
        |--------------------------------------------------------------------------
        | Evitamos recursión usando insert() en vez de create()
        */

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'pdf_exported',
            'auditable_type' => Audit::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'filename'   => $filename,
                'ids'        => $request->ids ?? null,
                'export_all' => $request->boolean('export_all'),
                'selected'   => $isSelectedExport,
                'total'      => $audits->count(),
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        $pdf = Pdf::loadView('admin.export.audits-pdf', [
            'audits'           => $audits,
            'isSelectedExport' => $isSelectedExport,
            'exportedBy'       => Auth::user()?->name . ' ' . Auth::user()?->last_name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    public function exportCsv(Request $request)
    {
        $ids = $request->has('export_all') ? null : $request->input('ids');

        $filename = 'auditorias_' . now()->format('Y-m-d_H-i-s') . '.csv';

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'csv_exported',
            'auditable_type' => Audit::class,
            'auditable_id'   => null,
            'old_values'     => null,
            'new_values'     => [
                'ids'        => $ids,
                'export_all' => $request->boolean('export_all', false),
                'filename'   => $filename,
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        return Excel::download(new AuditsCsvExport($ids), $filename, \Maatwebsite\Excel\Excel::CSV);
    }
}
