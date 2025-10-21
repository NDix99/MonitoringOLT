<?php

namespace App\Http\Controllers;

use App\Models\Olt;
use App\Models\Onu;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'admin_users' => User::where('role', 'admin')->count(),
            'total_olts' => Olt::count(),
            'active_olts' => Olt::where('is_active', true)->count(),
            'total_onus' => Onu::count(),
            'online_onus' => Onu::where('status_code', 3)->count(),
            'offline_onus' => Onu::where('status_code', 6)->count(),
        ];

        $users = User::latest()->paginate(10);
        $olts = Olt::withCount('onus')->latest()->paginate(10);

        return view('admin.dashboard', compact('stats', 'users', 'olts'));
    }

    public function users()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function oltDevices()
    {
        $olts = Olt::withCount('onus')->latest()->paginate(20);
        return view('admin.olts', compact('olts'));
    }

    public function toggleUserStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        
        return redirect()->back()->with('success', 
            'User ' . ($user->is_active ? 'activated' : 'deactivated') . ' successfully.'
        );
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
