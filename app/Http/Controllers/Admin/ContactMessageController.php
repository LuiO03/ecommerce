<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ContactMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:contact-messages.index')->only(['index']);
        $this->middleware('can:contact-messages.view')->only(['show']);
        $this->middleware('can:contact-messages.reply')->only(['updateStatus']);
        $this->middleware('can:contact-messages.delete')->only(['destroy', 'destroyMultiple']);
    }

    public function index()
    {
        $messages = ContactMessage::query()
            ->select([
                'id',
                'name',
                'email',
                'topic',
                'order_number',
                'response',
                'status',
                'created_at',
                'read_at',
                'replied_at',
            ])
            ->orderByDesc('id')
            ->get();

        return view('admin.contact-messages.index', compact('messages'));
    }

    public function show(ContactMessage $contactMessage)
    {
        return response()->json([
            'id' => $contactMessage->id,
            'name' => $contactMessage->name,
            'email' => $contactMessage->email,
            'topic' => $contactMessage->topic,
            'order_number' => $contactMessage->order_number,
            'message' => $contactMessage->message,
            'response' => $contactMessage->response,
            'status' => $contactMessage->status,
            'read_at' => $contactMessage->read_at?->format('d/m/Y H:i'),
            'replied_at' => $contactMessage->replied_at?->format('d/m/Y H:i'),
            'created_at' => $contactMessage->created_at?->format('d/m/Y H:i'),
        ]);
    }

    public function updateResponse(Request $request, ContactMessage $contactMessage)
    {
        $validated = $request->validate([
            'response' => 'required|string|min:3|max:8000',
        ]);

        $contactMessage->update([
            'response' => $validated['response'],
            'status' => 'replied',
            'read_at' => $contactMessage->read_at ?? now(),
            'replied_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Respuesta guardada correctamente.',
        ]);
    }

    public function updateStatus(Request $request, ContactMessage $contactMessage)
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
            $payload['read_at'] = $contactMessage->read_at ?? now();
            $payload['replied_at'] = null;
        }

        if ($status === 'replied') {
            $payload['read_at'] = $contactMessage->read_at ?? now();
            $payload['replied_at'] = now();
        }

        if ($status !== 'replied') {
            $payload['response'] = $contactMessage->response;
        }

        $contactMessage->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente.',
        ]);
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $name = $contactMessage->name;

        $contactMessage->delete();

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registro eliminado',
            'title' => 'Mensaje eliminado',
            'message' => "El mensaje de <strong>{$name}</strong> fue eliminado.",
        ]);

        return redirect()->route('admin.contact-messages.index');
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'messages' => 'sometimes|array|min:1',
            'messages.*' => 'exists:contact_messages,id',
            'ids' => 'sometimes|array|min:1',
            'ids.*' => 'exists:contact_messages,id',
        ]);

        $messageIds = $request->messages ?? $request->ids;

        if (empty($messageIds)) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'Sin selección',
                'message' => 'No seleccionaste mensajes para eliminar.',
            ]);

            return redirect()->route('admin.contact-messages.index');
        }

        $messages = ContactMessage::whereIn('id', $messageIds)->get();

        if ($messages->isEmpty()) {
            Session::flash('info', [
                'type' => 'danger',
                'header' => 'Error',
                'title' => 'No encontrados',
                'message' => 'Los mensajes seleccionados no existen.',
            ]);

            return redirect()->route('admin.contact-messages.index');
        }

        $names = $messages->pluck('name')->all();

        ContactMessage::whereIn('id', $messageIds)->delete();

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Eliminación múltiple',
            'title' => 'Mensajes eliminados',
            'message' => 'Se eliminaron los siguientes mensajes de contacto:',
            'list' => $names,
        ]);

        return redirect()->route('admin.contact-messages.index');
    }
}
