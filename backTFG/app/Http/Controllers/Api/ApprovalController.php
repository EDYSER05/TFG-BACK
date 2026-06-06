<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApprovalResource;
use App\Models\Approval;
use App\Http\Requests\StoreApprovalRequest;
use App\Http\Requests\UpdateApprovalRequest;
use App\Services\NotificationService;
use Exception;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Approval::with(['approvedBy', 'absenceRequest.user'])->orderBy('created_at', 'desc');

            // ?approved_by
            if ($request->approved_by) {
                $query->where('approved_by', $request->approved_by);
            }

            // ?absence_request_id
            if ($request->absence_request_id) {
                $query->where('absence_request_id', $request->absence_request_id);
            }

            return ApprovalResource::collection($query->get());
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al obtener las aprobaciones', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Approval $approval)
    {
        try {
            $approval->load(['approvedBy', 'absenceRequest.user', 'absenceRequest.absenceType']);

            return new ApprovalResource($approval);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al obtener la aprobación', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreApprovalRequest $request)
    {
        try {
            $data = $request->validated();

            $approval = Approval::create($data);
            $approval->absenceRequest->update(['status' => $data['status']]);
            $approval->load(['approvedBy', 'absenceRequest.absenceType']);

            // Notificamos al empleado del resultado de su solicitud
            $absenceReq = $approval->absenceRequest;
            if ($absenceReq) {
                $label    = $data['status'] === 'approved' ? 'aprobada' : 'rechazada';
                $typeName = $absenceReq->absenceType?->name ?? 'ausencia';
                NotificationService::send(
                    $absenceReq->user_id,
                    "Tu solicitud de {$typeName} del {$absenceReq->start_date} al {$absenceReq->end_date} ha sido {$label}.",
                    'absence'
                );
            }

            return (new ApprovalResource($approval))
                ->additional(['msg' => 'Aprobación registrada correctamente'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al registrar la aprobación', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateApprovalRequest $request, Approval $approval)
    {
        try {
            $data = $request->validated();

            $approval->update($data);

            if (isset($data['status'])) {
                $approval->absenceRequest->update(['status' => $data['status']]);
            }

            $approval->load(['approvedBy', 'absenceRequest']);

            return (new ApprovalResource($approval))->additional(['msg' => 'Aprobación actualizada correctamente']);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al actualizar la aprobación', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Approval $approval)
    {
        try {
            $approval->absenceRequest->update(['status' => 'pending']);
            $approval->delete();

            return response()->json(['msg' => 'Aprobación eliminada correctamente']);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al eliminar la aprobación', 'error' => $e->getMessage()], 500);
        }
    }
}
