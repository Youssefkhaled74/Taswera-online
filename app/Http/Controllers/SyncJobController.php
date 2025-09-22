<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\SyncJob;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\BranchResource;
use App\Http\Resources\SyncJobResource;
use Illuminate\Support\Facades\Validator;

class SyncJobController extends Controller
{
    public function branchesLastSync()
    {
        $branches = Branch::all();
        $result = [];
        foreach ($branches as $branch) {
            $lastSync = SyncJob::where('branch_id', $branch->id)
                ->where('status', 'synced')
                ->orderByDesc('created_at')
                ->first();
            $result[] = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'last_sync_job_id' => $lastSync ? $lastSync->id : null,
                'last_sync_time' => $lastSync ? $lastSync->created_at : null,
            ];
        }
        return response()->json([
            'message' => 'Branches with last sync info',
            'data' => $result,
        ], Response::HTTP_OK);
    }

    public function lastSyncJob()
    {
        $lastJob = SyncJob::where('status', 'synced')->orderByDesc('created_at')->first();
        if (!$lastJob) {
            return response()->json([
                'message' => 'No synced jobs found',
                'data' => null,
            ], Response::HTTP_OK);
        }
        return response()->json([
            'message' => 'Last synced job found',
            'data' => [
                'id' => $lastJob->id,
                'synced_at' => $lastJob->created_at,
            ],
        ], Response::HTTP_OK);
    }

    public function filterSyncJobs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'nullable|integer|exists:branches,id',
            'employee_id' => 'nullable|integer|exists:employees,id',
            'employeeName' => 'nullable|string|max:255',
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $query = SyncJob::query();

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }
        if ($request->has('employeeName')) {
            $query->where('employeeName', 'like', '%' . $request->input('employeeName') . '%');
        }
        if ($request->has('from') && $request->input('from')) {
            if ($request->has('to') && $request->input('to')) {
                // Filter by date range if both from and to are provided
                $query->whereDate('created_at', '>=', $request->input('from'))
                    ->whereDate('created_at', '<=', $request->input('to'));
            } else {
                // Filter by exact date if only from is provided
                $query->whereDate('created_at', $request->input('from'));
            }
        }

        $jobs = $query->get();
        $total_photos = $jobs->sum('number_of_photos');
        $total_money = $jobs->sum('pay_amount');

        // Calculate employee photos summary
        $employeePhotosSummary = $query->selectRaw('employee_id, branch_id, employeeName, SUM(number_of_photos) as total_photos')
            ->groupBy('employee_id', 'branch_id', 'employeeName')
            ->get()
            ->groupBy(function ($item) {
                // Group by employee_id and branch_id when employee_id is not null
                // Otherwise, group by employeeName and branch_id
                return $item->employee_id ? "emp_{$item->employee_id}_{$item->branch_id}" : "name_{$item->employeeName}_{$item->branch_id}";
            })
            ->map(function ($group) {
                $item = $group->first();
                return [
                    'employee_id' => $item->employee_id,
                    'branch_id' => $item->branch_id,
                    'employeeName' => $item->employeeName,
                    'total_photos' => (int) $group->sum('total_photos'),
                ];
            })->values()->toArray();

        return response()->json([
            'message' => 'Filtered sync jobs',
            'data' => [
                'jobs' => SyncJobResource::collection($jobs),
                'total_photos' => (int) $total_photos,
                'total_money' => (float) $total_money,
                'employee_photos_summary' => $employeePhotosSummary,
            ],
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|integer|exists:branches,id',
            'employee_id' => 'nullable|integer|exists:employees,id',
            'employeeName' => 'required|string|max:255',
            'pay_amount' => 'required|numeric|min:0',
            'orderprefixcode' => 'required|string|max:50',
            'status' => 'required|string|max:50',
            'shift_name' => 'required|string|max:100',
            'orderphone' => 'required|string|max:20',
            'number_of_photos' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $syncJob = SyncJob::create([
            'branch_id' => $request->input('branch_id'),
            'employee_id' => $request->input('employee_id'),
            'employeeName' => $request->input('employeeName'),
            'pay_amount' => $request->input('pay_amount'),
            'orderprefixcode' => $request->input('orderprefixcode'),
            'status' => $request->input('status'),
            'shift_name' => $request->input('shift_name'),
            'orderphone' => $request->input('orderphone'),
            'number_of_photos' => $request->input('number_of_photos'),
        ]);

        return response()->json([
            'message' => 'SyncJob created successfully',
            'data' => new SyncJobResource($syncJob),
        ], Response::HTTP_CREATED);
    }

    public function statistics(Request $request)
    {
        if (!$request->user()->is_admin) {
            return response()->json([
                'error' => 'Unauthorized: Admin access required',
            ], Response::HTTP_FORBIDDEN);
        }

        $query = SyncJob::query();

        if ($request->has('branch_id')) {
            $validator = Validator::make($request->all(), [
                'branch_id' => 'integer|exists:branches,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first(),
                ], Response::HTTP_BAD_REQUEST);
            }

            $query->where('branch_id', $request->input('branch_id'));
        }

        $statusQuery = SyncJob::query();
        if ($request->has('branch_id')) {
            $statusQuery->where('branch_id', $request->input('branch_id'));
        }

        $stats = [
            'total_jobs' => $query->count(),
            'total_pay_amount' => number_format($query->sum('pay_amount'), 2, '.', ''),
            'total_photos' => (int) $query->sum('number_of_photos'),
            'status_breakdown' => $statusQuery->groupBy('status')->selectRaw('status, COUNT(*) as count')->pluck('count', 'status')->toArray(),
            'jobs' => SyncJobResource::collection($query->get()),
        ];

        return response()->json([
            'message' => 'Statistics retrieved successfully',
            'data' => $stats,
        ], Response::HTTP_OK);
    }

    public function createBranch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:branches',
            'is_active' => 'boolean|in:0,1',
            'manager_email' => 'required|email|max:255',
            'manager_password' => 'required|string|min:6',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $branch = Branch::create([
            'name' => $request->input('name'),
            'token' => Str::random(32),
            'is_active' => true,
            'manager_email' => $request->input('manager_email'),
            'manager_password' => $request->input('manager_password'),
            'admin_email' => $request->input('admin_email'),
            'admin_password' => $request->input('admin_password'),
        ]);

        return response()->json([
            'message' => 'Branch created successfully',
            'data' => new BranchResource($branch),
        ], Response::HTTP_CREATED);
    }

    public function listBranches()
    {
        $branches = Branch::all();

        return response()->json([
            'message' => 'Branches retrieved successfully',
            'data' => BranchResource::collection($branches),
        ], Response::HTTP_OK);
    }

    public function listEmployees(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $query = SyncJob::query()->select('employee_id', 'branch_id', 'employeeName')
            ->distinct('employee_id', 'branch_id');

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        $employees = $query->get()->map(function ($item) {
            return [
                'employee_id' => $item->employee_id,
                'branch_id' => $item->branch_id,
                'employeeName' => $item->employeeName,
            ];
        })->values()->toArray();

        return response()->json([
            'message' => 'Employees retrieved successfully',
            'data' => $employees,
        ], Response::HTTP_OK);
    }
}
