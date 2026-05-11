<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClaimMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ClaimMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:claim-messages.index')->only(['index']);
        $this->middleware('can:claim-messages.view')->only(['show']);
        $this->middleware('can:claim-messages.reply')->only(['updateResponse', 'updateStatus']);
        $this->middleware('can:claim-messages.delete')->only(['destroy', 'destroyMultiple']);
    }

    public function index()
    {
        $claims = ClaimMessage::query()
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'claim_type',
                'response',
                'status',
                'created_at',
                'read_at',
                'replied_at',
            ])
            ->orderByDesc('id')
            ->get();

        return view('admin.claim-messages.index', compact('claims'));
    }

    public function show(ClaimMessage $claimMessage)
    {
        return response()->json([
            'id' => $claimMessage->id,
            'name' => $claimMessage->name,
            'email' => $claimMessage->email,
            'phone' => $claimMessage->phone,
            'claim_type' => $claimMessage->claim_type,
            'claim_detail' => $claimMessage->claim_detail,
            'message' => $claimMessage->claim_detail,
            'response' => $claimMessage->response,
            'status' => $claimMessage->status,
            'read_at' => $claimMessage->read_at?->format('d/m/Y H:i'),
            'replied_at' => $claimMessage->replied_at?->format('d/m/Y H:i'),
            'created_at' => $claimMessage->created_at?->format('d/m/Y H:i'),
        ]);
    }

    public function updateResponse(Request $request, ClaimMessage $claimMessage)
    {
        $validated = $request->validate([
            'response' => 'required|string|min:3|max:8000',
        ]);

        $claimMessage->update([
            'response' => $validated['response'],
            'status' => 'replied',
            'read_at' => $claimMessage->read_at ?? now(),
            'replied_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Respuesta guardada correctamente.',
        ]);
    }

    public function updateStatus(Request $request, ClaimMessage $claimMessage)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,read,replied',
        ]);

        $status = $validated['status'];

        $payload = ['status' => $status];

        if ($status === 'new') {
            $payload['read_at'] = null;
            $payload['replied_at'] = null;
        }

        if ($status === 'read') {
            $payload['read_at'] = $claimMessage->read_at ?? now();
            $payload['replied_at'] = null;
        }

        if ($status === 'replied') {
            $payload['read_at'] = $claimMessage->read_at ?? now();
            $payload['replied_at'] = now();
        }

        if ($status !== 'replied') {
            $payload['response'] = $claimMessage->response;
        }

        $claimMessage->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente.',
        ]);
    }

    public function destroy(ClaimMessage $claimMessage)
    {
        $name = $claimMessage->name;

        $claimMessage->delete();

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registro eliminado',
            'title' => 'Reclamo eliminado',
            'message' => "El reclamo de <strong>{$name}</strong> fue eliminado.",
        ]);

        return redirect()->route('admin.claim-messages.index');
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'claims' => 'sometimes|array|min:1',
            'claims.*' => 'exists:claim_messages,id',
            'ids' => 'sometimes|array|min:1',
            'ids.*' => 'exists:claim_messages,id',
        ]);

        $claimIds = $request->claims ?? $request->ids;

        if (empty($claimIds)) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'Sin selección',
                'message' => 'No seleccionaste reclamos para eliminar.',
            ]);

            return redirect()->route('admin.claim-messages.index');
        }

        $claims = ClaimMessage::whereIn('id', $claimIds)->get();

        if ($claims->isEmpty()) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'No encontrados',
                'message' => 'Los reclamos seleccionados no existen.',
            ]);

            return redirect()->route('admin.claim-messages.index');
        }

        $names = $claims->pluck('name')->all();

        ClaimMessage::whereIn('id', $claimIds)->delete();

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Eliminación múltiple',
            'title' => 'Reclamos eliminados',
            'message' => 'Se eliminaron los siguientes reclamos:',
            'list' => $names,
        ]);

        return redirect()->route('admin.claim-messages.index');
    }
}
