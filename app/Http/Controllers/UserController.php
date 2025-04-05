<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all(); // Fetch all users
        return view('users.index', compact('users')); // Pass users to the view
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = 'user';
        $user->is_active = true;

        if ($request->hasFile('photo')) {
            $filePath = $request->file('photo')->store('users', 'public');
            $user->photo = $filePath;
        }

        $user->save();

        // Trigger email verification
        event(new Registered($user));

        $details = "Name: {$user->name}\nEmail: {$user->email}\nRole: User\nStatus: Active";
        return redirect()->route('users.index')
            ->with('success', 'User created successfully. A verification email has been sent.')
            ->with('toast', [
                'type' => 'success',
                'message' => 'User Created Successfully',
                'details' => $details
            ]);
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email
        ];

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $filePath = $request->file('photo')->store('users', 'public');
            $user->photo = $filePath;
        }

        $user->save();

        $changes = [];
        if ($oldValues['name'] !== $user->name) {
            $changes[] = "Name: {$oldValues['name']} → {$user->name}";
        }
        if ($oldValues['email'] !== $user->email) {
            $changes[] = "Email: {$oldValues['email']} → {$user->email}";
        }
        if ($request->filled('password')) {
            $changes[] = "Password updated";
        }
        if ($request->hasFile('photo')) {
            $changes[] = "Profile photo updated";
        }

        $details = count($changes) > 0 ? implode("\n", $changes) : "No changes made";

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.')
            ->with('toast', [
                'type' => 'success',
                'message' => 'User Updated Successfully',
                'details' => $details
            ]);
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json(['error' => 'You cannot deactivate your own account.'], 403);
        }

        $oldStatus = $user->is_active ? 'Active' : 'Inactive';
        $user->is_active = !$user->is_active;
        $user->save();
        $newStatus = $user->is_active ? 'Active' : 'Inactive';

        return response()->json([
            'success' => true,
            'is_active' => $user->is_active,
            'toast' => [
                'type' => 'success',
                'message' => 'User Status Updated',
                'details' => "User: {$user->name}\nStatus: {$oldStatus} → {$newStatus}"
            ]
        ]);
    }

    public function toggleRole(User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json(['error' => 'You cannot change your own role.'], 403);
        }

        $oldRole = ucfirst($user->role);
        $user->role = $user->role === 'admin' ? 'user' : 'admin';
        $user->save();
        $newRole = ucfirst($user->role);

        return response()->json([
            'success' => true,
            'is_admin' => $user->role === 'admin',
            'toast' => [
                'type' => 'success',
                'message' => 'User Role Updated',
                'details' => "User: {$user->name}\nRole: {$oldRole} → {$newRole}"
            ]
        ]);
    }

    public function getData()
    {
        $users = User::select(['id', 'name', 'email', 'photo', 'is_active', 'role', 'email_verified_at']);

        return DataTables::of($users)
            ->addColumn('status_badge', function ($user) {
                $status = $user->is_active ? 'Active' : 'Inactive';
                $color = $user->is_active ? 'green' : 'red';
                return "<span class='px-2 py-1 text-{$color}-700 bg-{$color}-100 rounded-full'>{$status}</span>";
            })
            ->addColumn('role_badge', function ($user) {
                $role = $user->role === 'admin' ? 'Admin' : 'User';
                $color = $user->role === 'admin' ? 'purple' : 'blue';
                return "<span class='px-2 py-1 text-{$color}-700 bg-{$color}-100 rounded-full capitalize'>{$role}</span>";
            })
            ->addColumn('actions', function ($user) {
                $html = '<div class="flex space-x-2">';
                $html .= '<a href="' . route('users.edit', $user) . '" class="text-indigo-600 hover:text-indigo-900">Edit</a>';
                
                if (Auth::user()->role === 'admin' && Auth::id() !== $user->id) {
                    $html .= '<button onclick="toggleUserStatus(' . $user->id . ')" class="text-blue-600 hover:text-blue-900">Toggle Status</button>';
                    $html .= '<button onclick="toggleUserRole(' . $user->id . ')" class="text-purple-600 hover:text-purple-900">Toggle Role</button>';
                }
                
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['status_badge', 'role_badge', 'actions'])
            ->make(true);
    }
}
