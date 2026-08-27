<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::with(['role', 'assignedCustomers', 'calls'])->get();

        return response()->json([
            'success' => true,
            'data' => $employees,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string',
            'department' => 'nullable|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $employee = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully',
            'employee' => $employee->load('role'),
        ]);
    }

    public function show($id)
    {
        $employee = User::with(['role', 'assignedCustomers', 'calls.customer', 'appointments.customer'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'employee' => $employee,
        ]);
    }

    public function update(Request $request, $id)
    {
        $employee = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'role_id' => 'nullable|exists:roles,id',
            'phone' => 'nullable|string',
            'department' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $employee->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
            'employee' => $employee->load('role'),
        ]);
    }

    public function roles()
    {
        return response()->json([
            'success' => true,
            'roles' => Role::all(),
        ]);
    }
}
