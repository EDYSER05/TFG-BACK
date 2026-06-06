<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AbsenceRequestResource;
use App\Models\AbsenceRequest;
use App\Models\User;
use App\Http\Requests\StoreAbsenceRequestRequest;
use App\Http\Requests\UpdateAbsenceRequestRequest;
use App\Services\NotificationService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class AbsenceRequestController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = AbsenceRequest::with(['user.department', 'absenceType'])->orderBy('created_at', 'desc');

            // ?user_id / ?department_id / ?company_id
            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            } elseif ($request->department_id || $request->company_id) {
                $query->whereIn('user_id', $this->userIdsForScope($request));
            }

            // ?absence_type_id
            if ($request->absence_type_id) {
                $query->where('absence_type_id', $request->absence_type_id);
            }

            // ?status
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // ?date_from
            if ($request->date_from) {
                $query->whereDate('start_date', '>=', $request->date_from);
            }

            // ?date_to
            if ($request->date_to) {
                $query->whereDate('end_date', '<=', $request->date_to);
            }

            return AbsenceRequestResource::collection($query->limit(500)->get());
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al obtener las solicitudes de ausencia', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(AbsenceRequest $absenceRequest)
    {
        try {
            $absenceRequest->load(['user', 'absenceType', 'approval.approvedBy']);

            return new AbsenceRequestResource($absenceRequest);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al obtener la solicitud de ausencia', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreAbsenceRequestRequest $request)
    {
        try {
            $data = $request->validated();
            $data['status'] = 'pending';

            $absenceRequest = AbsenceRequest::create($data);
            $absenceRequest->load(['user.department.company', 'absenceType']);

            // Notificamos a los responsables del empleado que solicitó la ausencia
            $employee   = $absenceRequest->user;
            $department = $employee?->department;
            $company    = $department?->company;

            $toNotify = collect();

            // Manager del departamento
            if ($department?->manager_id && $department->manager_id !== $employee?->id) {
                $toNotify->push($department->manager_id);
            }

            // Owner de la empresa
            if ($company?->owner_id && $company->owner_id !== $employee?->id) {
                $toNotify->push($company->owner_id);
            }

            // Usuarios HR de la misma empresa
            if ($company) {
                $hrIds = User::whereHas('role', fn($q) => $q->where('name', 'hr'))
                    ->whereHas('department', fn($q) => $q->where('company_id', $company->id))
                    ->where('id', '!=', $employee?->id)
                    ->pluck('id');
                $toNotify = $toNotify->merge($hrIds);
            }

            // Administradores del sistema
            $adminIds = User::whereHas('role', fn($q) => $q->where('name', 'admin'))
                ->where('id', '!=', $employee?->id)
                ->pluck('id');
            $toNotify = $toNotify->merge($adminIds);

            $typeName     = $absenceRequest->absenceType?->name ?? 'ausencia';
            $employeeName = $employee ? "{$employee->name} {$employee->last_name}" : 'Un empleado';
            $message      = "{$employeeName} ha solicitado {$typeName}.";

            foreach ($toNotify->unique() as $userId) {
                NotificationService::send((int) $userId, $message, 'absence');
            }

            return (new AbsenceRequestResource($absenceRequest))
                ->additional(['msg' => 'Solicitud de ausencia creada correctamente'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al crear la solicitud de ausencia', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateAbsenceRequestRequest $request, AbsenceRequest $absenceRequest)
    {
        try {
            $data = $request->validated();

            $previousStatus = $absenceRequest->status;
            $absenceRequest->load('absenceType');
            $absenceRequest->update($data);
            $absenceRequest->load(['user', 'absenceType', 'approvals']);

            if (isset($data['status']) && $data['status'] !== $previousStatus) {
                $label    = $data['status'] === 'approved' ? 'aprobada' : 'rechazada';
                $typeName = $absenceRequest->absenceType?->name ?? 'ausencia';
                $desde    = $absenceRequest->start_date;
                $hasta    = $absenceRequest->end_date;
                NotificationService::send(
                    $absenceRequest->user_id,
                    "Tu solicitud de {$typeName} del {$desde} al {$hasta} ha sido {$label}.",
                    'absence'
                );
            }

            return (new AbsenceRequestResource($absenceRequest))->additional(['msg' => 'Solicitud actualizada correctamente']);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al actualizar la solicitud de ausencia', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, AbsenceRequest $absenceRequest)
    {
        try {
            $authUser = $request->user();
            if ($authUser?->role?->name === 'employee') {
                if ($absenceRequest->user_id !== $authUser->id) {
                    return response()->json(['msg' => 'No tienes permiso para cancelar esta solicitud'], 403);
                }
                if ($absenceRequest->status !== 'pending') {
                    return response()->json(['msg' => 'Solo puedes cancelar solicitudes pendientes'], 422);
                }
            }

            $absenceRequest->delete();

            return response()->json(['msg' => 'Solicitud de ausencia eliminada correctamente']);
        } catch (QueryException $e) {
            return response()->json(['msg' => 'No se puede eliminar la solicitud porque tiene registros relacionados'], 409);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al eliminar la solicitud de ausencia', 'error' => $e->getMessage()], 500);
        }
    }
}
