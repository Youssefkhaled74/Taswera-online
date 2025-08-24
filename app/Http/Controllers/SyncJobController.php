<?php

namespace App\Http\Controllers;

use App\Http\Resources\BranchResource;
use App\Http\Resources\SyncJobResource;
use App\Models\Branch;
use App\Models\SyncJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class SyncJobController extends Controller
{
    /**
     * Store a new SyncJob record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|integer|exists:branches,id',
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

    /**
     * Retrieve SyncJob statistics, optionally filtered by branch_id.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

        // Use a separate query for status_breakdown to avoid affecting the main query
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

    /**
     * Create a new branch.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createBranch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:branches',
            'is_active' => 'boolean|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $branch = Branch::create([
            'name' => $request->input('name'),
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Branch created successfully',
            'data' => new BranchResource($branch),
        ], Response::HTTP_CREATED);
    }

    /**
     * List all branches.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listBranches()
    {
        $branches = Branch::select('id', 'name')->get();

        return response()->json([
            'message' => 'Branches retrieved successfully',
            'data' => BranchResource::collection($branches),
        ], Response::HTTP_OK);
    }
}
