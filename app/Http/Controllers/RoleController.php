<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Roles/Index');
    }

    public function getData()
    {
        Log::info('test');
        $users = Role::all();

        return DataTables::of($users)
            ->addColumn('check', function ($row) {
                return '<div class="custom-control custom-checkbox item-check">
            <input type="checkbox" class="form-check-input" id="' . $row->id . '" value="' . $row->id . '">
            <label class="form-check-label form-check-label" for="' . $row->id . '"></label>
          </div>';
            })
            ->addColumn('action',  function ($row) {})

            ->addColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge badge-success badge-pill">Active</span>';
                } else {
                    return '<span class="badge badge-danger badge-pill">Inactive</span>';
                }
            })
            ->rawColumns(['check', 'action', 'status'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $allPermissions = $this->getAllPermissions();

        return Inertia::render('Roles/CreateUpdate', ['allPermissions' => $allPermissions]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'unique:roles,name'],
            'dashboard_type'  => ['required']
        ]);

        try {
            DB::beginTransaction();
            Role::create(['guard_name' => 'web', 'name' => $request->name, 'dashboard_type' => $request->dashboard_type]);
            DB::commit();
            return redirect(route('roles'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            abort(500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $role = Role::find($id);
        return Inertia::render('Roles/CreateUpdate', ['role' => $role]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => ['required', 'unique:roles,name,' . $request->id],
               'dashboard_type'  => ['required']
        ]);

        try {
            DB::beginTransaction();
            $role = Role::find($request->id);
            $role->name = $request->name;
            $role->dashboard_type = $request->dashboard_type;
            $role->save();
            DB::commit();
            return redirect()->route('roles');
        } catch (Exception $ex) {
            // dd($ex);
            DB::rollBack();
            Log::error($ex);
            abort(500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editPermissions($id)
    {
        $role = Role::with('permissions')->find($id);
        $allPermissions = $this->getAllPermissions();
        return Inertia::render('Roles/Permissions', ['role' => $role, 'allPermissions' => $allPermissions]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePermissions(Request $request)
    {
        try {
            DB::beginTransaction();
            $role = Role::find($request->id);
            $role->syncPermissions($request->permissions);
            DB::commit();
            return redirect()->route('roles');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            abort(500);
        }
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Role::destroy($id);
            return redirect()->route('roles');
        } catch (Exception $ex) {
            Log::error($ex);
            return abort(500);
        }
    }


    public function getAllPermissions()
    {
        return Permission::select(
            'id',
            'section_name',
            'name',
            DB::raw("CONCAT(UPPER(SUBSTRING(REPLACE(name, '.', ' '),1,1)),LOWER(SUBSTRING(REPLACE(name, '.', ' '),2))) as ft_name")
        )->get()->groupBy('section_name');
    }
}
