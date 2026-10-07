<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $module = $request->query('module');
        $query = AuditLog::latest();

        if ($module) {
            $query->where('module', $module);
        }

        $logs = $query->paginate(25);
        $modules = AuditLog::select('module')->distinct()->pluck('module');

        return view('audit-logs.index', compact('logs', 'modules', 'module'));
    }
}
