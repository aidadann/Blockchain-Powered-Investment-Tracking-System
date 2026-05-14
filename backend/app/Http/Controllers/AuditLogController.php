<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('user')->latest()->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Audit logs fetched successfully',
            'data' => $logs
        ], 200);
    }
}
