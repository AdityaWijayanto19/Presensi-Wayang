<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Enums\ActivityModule;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminActivityLog::query()
            ->with('user:id,name')
            ->latest('created_at')
            ->latest('id');

        if ($request->filled('user')) {
            $query->where('user_id', $request->integer('user'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->input('from'))) {
            $query->where('created_at', '>=', $request->input('from').' 00:00:00');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->input('to'))) {
            $query->where('created_at', '<=', $request->input('to').' 23:59:59');
        }

        if ($request->filled('q')) {
            $search = '%'.$request->input('q').'%';
            $query->where(function ($builder) use ($search) {
                $builder->where('description', 'like', $search)
                    ->orWhere('user_name', 'like', $search)
                    ->orWhere('path', 'like', $search);
            });
        }

        $logs = $query->paginate(15)->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name']);
        $actions = ActivityAction::cases();
        $modules = ActivityModule::cases();

        return view('admin.activitylog.index', compact('logs', 'users', 'actions', 'modules'));
    }
}
