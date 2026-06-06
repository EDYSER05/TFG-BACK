<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatMessageResource;
use App\Models\ChatMessage;
use Exception;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ChatMessage::with(['sender'])->orderBy('created_at', 'asc');

            if ($request->employee_id) {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->company_id) {
                $query->where('company_id', $request->company_id);
            }

            return ChatMessageResource::collection($query->get());
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al obtener los mensajes', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'company_id'  => 'required|exists:companies,id',
                'employee_id' => 'required|exists:users,id',
                'sender_id'   => 'required|exists:users,id',
                'message'     => 'required|string|max:1000',
            ]);

            $msg = ChatMessage::create($data);
            $msg->load(['sender']);

            return (new ChatMessageResource($msg))->response()->setStatusCode(201);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al enviar el mensaje', 'error' => $e->getMessage()], 500);
        }
    }

    // Marca como leídos los mensajes de una conversación para el receptor actual
    public function markRead(Request $request)
    {
        try {
            $data = $request->validate([
                'employee_id' => 'required|exists:users,id',
                'reader'      => 'required|in:employee,hr',
            ]);

            // Si el lector es el empleado, marca como leídos los mensajes NO enviados por él
            // Si el lector es RRHH, marca como leídos los mensajes enviados por el empleado
            ChatMessage::where('employee_id', $data['employee_id'])
                ->where('is_read', false)
                ->when(
                    $data['reader'] === 'employee',
                    fn ($q) => $q->where('sender_id', '!=', $data['employee_id']),
                    fn ($q) => $q->where('sender_id', $data['employee_id'])
                )
                ->update(['is_read' => true]);

            return response()->json(['msg' => 'ok']);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al marcar mensajes', 'error' => $e->getMessage()], 500);
        }
    }
}
