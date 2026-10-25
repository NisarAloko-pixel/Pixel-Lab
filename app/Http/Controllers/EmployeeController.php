<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('name')->get();

        return view('employees.index', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        Employee::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'employees' => Employee::orderBy('name')->get(['id', 'name', 'role', 'phone', 'balance']),
            ]);
        }

        return back()->with('success', 'Employee added.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'employees' => Employee::orderBy('name')->get(['id', 'name', 'role', 'phone', 'balance']),
            ]);
        }

        return back()->with('success', 'Employee removed.');
    }
}