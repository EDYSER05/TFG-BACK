<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TimeLogResource;
use App\Models\TimeLog;
use App\Http\Requests\StoreTimeLogRequest;
use App\Http\Requests\UpdateTimeLogRequest;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimeLogController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = TimeLog::with(['user'])->orderBy('date', 'desc');

            // ?user_id / ?department_id / ?company_id
            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            } elseif ($request->department_id || $request->company_id) {
                $query->whereIn('user_id', $this->userIdsForScope($request));
            }

            // ?date_from
            if ($request->date_from) {
                $query->whereDate('date', '>=', $request->date_from);
            }

            // ?date_to
            if ($request->date_to) {
                $query->whereDate('date', '<=', $request->date_to);
            }

            // Limit to last 500 records when no date filter is applied to avoid full-table scans
            if (!$request->date_from && !$request->date_to) {
                $query->limit(500);
            }

            return TimeLogResource::collection($query->get());
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al obtener los fichajes', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(TimeLog $timeLog)
    {
        try {
            $timeLog->load(['user', 'issues.issueType']);

            return new TimeLogResource($timeLog);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al obtener el fichaje', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreTimeLogRequest $request)
    {
        try {
            $data = $request->validated();

            // Combinamos la fecha con la hora para formar un DATETIME completo
            if (!empty($data['check_in'])) {
                $data['check_in'] = $data['date'] . ' ' . $data['check_in'];
            }
            if (!empty($data['check_out'])) {
                $data['check_out'] = $data['date'] . ' ' . $data['check_out'];
            }

            $timeLog = TimeLog::create($data);
            $timeLog->load(['user']);

            return (new TimeLogResource($timeLog))
                ->additional(['msg' => 'Fichaje creado correctamente'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al crear el fichaje', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateTimeLogRequest $request, TimeLog $timeLog)
    {
        try {
            $data = $request->validated();

            $date = Carbon::parse($timeLog->date)->format('Y-m-d');

            if (!empty($data['check_in'])) {
                $data['check_in'] = $date . ' ' . $data['check_in'];
            }
            if (!empty($data['check_out'])) {
                $data['check_out'] = $date . ' ' . $data['check_out'];
            }

            $timeLog->update($data);
            $timeLog->load(['user']);

            return (new TimeLogResource($timeLog))->additional(['msg' => 'Fichaje actualizado correctamente']);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al actualizar el fichaje', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(TimeLog $timeLog)
    {
        try {
            $timeLog->delete();

            return response()->json(['msg' => 'Fichaje eliminado correctamente']);
        } catch (QueryException $e) {
            return response()->json(['msg' => 'No se puede eliminar el fichaje porque tiene registros relacionados'], 409);
        } catch (Exception $e) {
            return response()->json(['msg' => 'Error al eliminar el fichaje', 'error' => $e->getMessage()], 500);
        }
    }
}
