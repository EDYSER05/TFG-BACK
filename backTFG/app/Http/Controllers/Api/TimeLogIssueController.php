<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TimeLogIssueResource;
use App\Models\TimeLogIssue;
use App\Models\User;
use App\Http\Requests\StoreTimeLogIssueRequest;
use App\Http\Requests\UpdateTimeLogIssueRequest;
use App\Services\NotificationService;
use Exception;
use Illuminate\Http\Request;

class TimeLogIssueController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = TimeLogIssue::with(['reportedBy.department', 'issueType', 'timeLog.user'])->orderBy('created_at', 'desc');

            // ?time_log_id
            if ($request->time_log_id) {
                $query->where('time_log_id', $request->time_log_id);
            }

            // ?user_id / ?department_id / ?company_id
            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            } elseif ($request->department_id || $request->company_id) {
                $query->whereIn('user_id', $this->userIdsForScope($request));
            }

            // ?issue_type_id
            if ($request->issue_type_id) {
                $query->where('issue_type_id', $request->issue_type_id);
            }

            // ?resolved
            if ($request->has('resolved')) {
                $query->where('resolved', $request->boolean('resolved'));
            }

            return TimeLogIssueResource::collection($query->get());
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al obtener las incidencias', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(TimeLogIssue $timeLogIssue)
    {
        try {
            $timeLogIssue->load(['reportedBy', 'issueType', 'timeLog.user']);

            return new TimeLogIssueResource($timeLogIssue);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al obtener la incidencia', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreTimeLogIssueRequest $request)
    {
        try {
            $data = $request->validated();

            $issue = TimeLogIssue::create($data);
            $issue->load(['reportedBy.department.company', 'issueType', 'timeLog']);

            // Notificamos a los responsables del empleado que reportó la incidencia
            $reporter   = $issue->reportedBy;
            $department = $reporter?->department;
            $company    = $department?->company;

            $toNotify = collect();

            // Manager del departamento
            if ($department?->manager_id && $department->manager_id !== $reporter?->id) {
                $toNotify->push($department->manager_id);
            }

            // Owner de la empresa
            if ($company?->owner_id && $company->owner_id !== $reporter?->id) {
                $toNotify->push($company->owner_id);
            }

            // Usuarios HR de la misma empresa
            if ($company) {
                $hrIds = User::whereHas('role', fn($q) => $q->where('name', 'hr'))
                    ->whereHas('department', fn($q) => $q->where('company_id', $company->id))
                    ->where('id', '!=', $reporter?->id)
                    ->pluck('id');
                $toNotify = $toNotify->merge($hrIds);
            }

            // Administradores del sistema
            $adminIds = User::whereHas('role', fn($q) => $q->where('name', 'admin'))
                ->where('id', '!=', $reporter?->id)
                ->pluck('id');
            $toNotify = $toNotify->merge($adminIds);

            $typeName     = $issue->issueType?->name ?? 'fichaje';
            $employeeName = $reporter ? "{$reporter->name} {$reporter->last_name}" : 'Un empleado';
            $message      = "{$employeeName} ha reportado una incidencia de {$typeName}.";

            foreach ($toNotify->unique() as $userId) {
                NotificationService::send((int) $userId, $message, 'issue');
            }

            return (new TimeLogIssueResource($issue))
                ->additional(['msg' => 'Incidencia creada correctamente'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al crear la incidencia', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateTimeLogIssueRequest $request, TimeLogIssue $timeLogIssue)
    {
        try {
            $data = $request->validated();

            $wasResolved = $timeLogIssue->resolved;
            $timeLogIssue->update($data);
            $timeLogIssue->load(['reportedBy', 'issueType', 'timeLog.user']);

            if (isset($data['resolved']) && $data['resolved'] && !$wasResolved) {
                $userId = $timeLogIssue->timeLog?->user_id;
                if ($userId) {
                    NotificationService::send(
                        $userId,
                        'Tu incidencia de fichaje ha sido resuelta.',
                        'issue'
                    );
                }
            }

            return (new TimeLogIssueResource($timeLogIssue))->additional(['msg' => 'Incidencia actualizada correctamente']);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al actualizar la incidencia', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(TimeLogIssue $timeLogIssue)
    {
        try {
            $timeLogIssue->delete();

            return response()->json(['msg' => 'Incidencia eliminada correctamente']);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al eliminar la incidencia', 'error' => $e->getMessage()], 500);
        }
    }
}
