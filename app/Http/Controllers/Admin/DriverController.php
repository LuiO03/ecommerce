<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:conductores.index')->only(['index']);
        $this->middleware('can:conductores.create')->only(['create', 'store']);
        $this->middleware('can:conductores.edit')->only(['edit', 'update']);
        $this->middleware('can:conductores.delete')->only(['destroy', 'destroyMultiple']);
    }

    public function index()
    {
        $drivers = Driver::with('user')
            ->orderByDesc('id')
            ->get();

        return view('admin.drivers.index', compact('drivers'));
    }

    public function create()
    {
        // Solo usuarios que aún no tienen perfil de conductor ni cliente
        $users = User::whereDoesntHave('driver')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'Cliente');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'last_name', 'email', 'phone']);

        return view('admin.drivers.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'       => ['required', 'exists:users,id', 'unique:drivers,user_id'],
            'vehicle_type'  => ['required', 'in:motorcycle,car'],
            'vehicle_plate' => ['nullable', 'string', 'max:20', 'unique:drivers,vehicle_plate'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'status'        => ['required', 'in:available,busy,inactive'],
        ]);

        $driver = Driver::create([
            'user_id'       => $request->user_id,
            'vehicle_type'  => $request->vehicle_type,
            'vehicle_plate' => $request->vehicle_plate,
            'phone'         => $request->phone,
            'status'        => $request->status,
            'created_by'    => Auth::id(),
            'updated_by'    => Auth::id(),
        ]);

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'created',
            'auditable_type' => Driver::class,
            'auditable_id'   => $driver->id,
            'old_values'     => null,
            'new_values'     => $driver->toArray(),
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        Session::flash('toast', [
            'type'    => 'success',
            'title'   => 'Conductor creado',
            'message' => 'El conductor se creó correctamente.',
        ]);

        Session::flash('highlightRow', $driver->id);

        return redirect()->route('admin.drivers.index');
    }

    public function edit(Driver $driver)
    {
        // Permitir mantener el usuario actual en el listado
        $users = User::whereDoesntHave('driver')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'Cliente');
            })
            ->orWhere('id', $driver->user_id)
            ->orderBy('name')
            ->get(['id', 'name', 'last_name', 'email', 'phone']);

        return view('admin.drivers.edit', compact('driver', 'users'));
    }

    public function update(Request $request, Driver $driver)
    {
        $request->validate([
            'user_id'       => ['required', 'exists:users,id', 'unique:drivers,user_id,' . $driver->id],
            'vehicle_type'  => ['required', 'in:motorcycle,car'],
            'vehicle_plate' => ['nullable', 'string', 'max:20', 'unique:drivers,vehicle_plate,' . $driver->id],
            'phone'         => ['nullable', 'string', 'max:20'],
            'status'        => ['required', 'in:available,busy,inactive'],
        ]);

        $oldValues = $driver->getOriginal();

        $driver->update([
            'user_id'       => $request->user_id,
            'vehicle_type'  => $request->vehicle_type,
            'vehicle_plate' => $request->vehicle_plate,
            'phone'         => $request->phone,
            'status'        => $request->status,
            'updated_by'    => Auth::id(),
        ]);

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'updated',
            'auditable_type' => Driver::class,
            'auditable_id'   => $driver->id,
            'old_values'     => $oldValues,
            'new_values'     => $driver->toArray(),
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        Session::flash('toast', [
            'type'    => 'success',
            'title'   => 'Conductor actualizado',
            'message' => 'Los datos del conductor se actualizaron correctamente.',
        ]);

        Session::flash('highlightRow', $driver->id);

        return redirect()->route('admin.drivers.index');
    }

    public function destroy(Request $request, Driver $driver)
    {
        $oldValues = $driver->toArray();

        $driver->delete();

        Audit::create([
            'user_id'        => Auth::id(),
            'event'          => 'deleted',
            'auditable_type' => Driver::class,
            'auditable_id'   => $driver->id,
            'old_values'     => $oldValues,
            'new_values'     => null,
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        Session::flash('toast', [
            'type'    => 'success',
            'title'   => 'Conductor eliminado',
            'message' => 'El conductor se eliminó correctamente.',
        ]);

        return redirect()->route('admin.drivers.index');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No se seleccionaron conductores para eliminar.');
        }

        $drivers = Driver::whereIn('id', $ids)->get();

        foreach ($drivers as $driver) {
            $oldValues = $driver->toArray();
            $driver->delete();

            Audit::create([
                'user_id'        => Auth::id(),
                'event'          => 'deleted',
                'auditable_type' => Driver::class,
                'auditable_id'   => $driver->id,
                'old_values'     => $oldValues,
                'new_values'     => null,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
            ]);
        }

        Session::flash('toast', [
            'type'    => 'success',
            'title'   => 'Conductores eliminados',
            'message' => 'Los conductores seleccionados se eliminaron correctamente.',
        ]);

        return redirect()->route('admin.drivers.index');
    }
}
