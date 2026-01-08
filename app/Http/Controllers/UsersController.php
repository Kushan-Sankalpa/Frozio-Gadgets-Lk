<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Service;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UsersController extends Controller
{
    /**
     * List page.
     */
    public function index()
    {
        return Inertia::render('Users/Index');
    }

    /**
     * Create form.
     */
    public function create()
    {
        $allRoles = Role::all();
        $services = Service::all();
        $branches = Branch::all();
        return Inertia::render('Users/CreateUpdate', ['allRoles' => $allRoles, 'services' => $services, 'branches' => $branches]);
    }

    /**
     * Store user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed'],
            'role_id'     => ['required'],
            'avatar'   => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],

        ]);

        try {
            DB::beginTransaction();

            $user = new User();
            $user->name   = $request->name;
            $user->email  = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();
            if ($request->hasFile('avatar')) {
                $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
            }

            $user->services()->sync($request->selected_services ?? []);
            $user->branches()->sync($request->branches ?? []);
            DB::commit();

            $role = Role::find($request->role_id);
            $user->assignRole($role->name);




            return redirect()->route('users');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
            return abort(500);
        }
    }

    /**
     * DataTables server data.
     */
    public function getData(Request $request)
    {
        $users = User::query();


        return DataTables::of($users)

            ->addColumn('avatar_url', fn($row) => $row->getFirstMediaUrl('avatar') ?: null)

            ->editColumn('created_at', fn($u) => optional($u->created_at)->toDateString())
            ->addColumn('check', function ($row) {
                return '<div class="custom-control custom-checkbox item-check">
                <input type="checkbox" class="form-check-input" id="' . $row->id . '" value="' . $row->id . '">
                <label class="form-check-label" for="' . $row->id . '"></label>
            </div>';
            })
            ->addColumn('role', fn($row) => e($row->roles[0]->name ?? '—'))
            ->addColumn('status', function ($row) {
                if ($row->deleted_at) {
                    return '<span class="badge bg-danger">Suspended</span>';
                }
                if ((int)$row->status === 1) {
                    return '<span class="badge bg-success">Active</span>';
                }
                return '<span class="badge bg-warning">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                $action_html = '';
                if (auth()->user()->can('user.view')) {
                    $action_html .= '<a class="dropdown-item action_edit" style="font-size:14px;padding:5px 13px;" data-item-id="' . $row->id . '" href="javascript:void(0)">
                    <i class="fas fa-edit me-2"></i> View / Edit
                </a>';
                }
                if (auth()->user()->can('users.edit')) {
                    $toggleLabel = ($row->status == 1 ? ' Deactivate' : ' Activate');
                    $toggleClass = ($row->status == 1 ? 'text-warning' : 'text-success');
                    $action_html .= '<a class="dropdown-item ' . $toggleClass . ' action_status_change" style="font-size:14px;padding:5px 13px;" data-item-id="' . $row->id . '" data-status="' . $row->status . '" href="javascript:void(0)">
                    <i class="fas fa-power-off me-2"></i>' . $toggleLabel . '
                </a>';
                }
                $action_html .= '<div class="dropdown-divider"></div>';
                if (auth()->user()->can('users.delete')) {
                    $action_html .= '<a class="dropdown-item text-danger action_delete" data-bs-toggle="modal" data-bs-target="#deleteConfirm" style="font-size:14px;padding:5px 13px;" data-item-id="' . $row->id . '" href="javascript:void(0)">
                    <i class="fas fa-trash me-2"></i> Delete
                </a>';
                }
                return '<div class="btn-group">
                <button type="button" class="btn btn-main btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Action</button>
                <div class="dropdown-menu" style="min-width:10rem;">' . $action_html . '</div>
            </div>';
            })

            // 🔎 real search
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');
                if (!$search) return;

                $s = trim($search);

                $query->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
                });

                $l = Str::lower($s);
                if (Str::contains($l, 'suspend')) {
                    $query->orWhereNotNull('deleted_at');
                } elseif (Str::contains($l, 'active')) {
                    $query->orWhere(function ($q) {
                        $q->where('status', 1)->whereNull('deleted_at');
                    });
                } elseif (Str::contains($l, 'inactive')) {
                    $query->orWhere(function ($q) {
                        $q->where('status', 0)->whereNull('deleted_at');
                    });
                }
            }, true)

            ->rawColumns(['check', 'status', 'role', 'action'])
            ->make(true);
    }
    /**
     * Edit form.
     */
    public function edit($id)
    {
        $user = User::with('media', 'roles', 'services', 'branches')->findOrFail($id);

        $avatar = $user->getFirstMedia('avatar');
        $user->setAttribute('avatar_url', $avatar ? $avatar->getFullUrl() : null);
        $allRoles = Role::all();
        $services = Service::all();
        $branches = Branch::all();
        return Inertia::render('Users/CreateUpdate', ['user' => $user, 'allRoles' => $allRoles, 'services' => $services, 'branches' => $branches]);
    }

    /**
     * Toggle active/inactive.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id'     => ['required', 'exists:users,id'],
            'status' => ['required', 'in:0,1'],
        ]);

        try {
            $user = User::findOrFail($request->id);
            $user->status = (int) !$request->boolean('status');

            $user->save();

            return redirect()->route('users');
        } catch (Exception $ex) {
            Log::error($ex);
            return abort(500);
        }
    }

    /**
     * Update user.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string'],
            'email'    => ['required', 'email', 'unique:users,email,' . $request->id],
            'role_id'     => ['required'],
            'avatar'   => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->id);
            $user->name   = $request->name;
            $user->email  = $request->email;


            $user->services()->sync($request->selected_services ?? []);
            $user->branches()->sync($request->branches ?? []);

            $user->save();

            if ($request->hasFile('avatar')) {
                $user->clearMediaCollection('avatar');
                $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
            }


            DB::commit();

            $role = Role::find($request->role_id);
            $user->syncRoles([$role->name]);

            return redirect()->route('users');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
            return abort(500);
        }
    }

    /**
     * Delete user.
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:users,id'],
        ]);

        try {
            User::destroy([$request->id]);
            return redirect()->route('users');
        } catch (Exception $ex) {
            Log::error($ex);
            return abort(500);
        }
    }


    public function updatePassword(Request $request)
    {
        $authUser = User::find(Auth::user()->id);
        $user = User::find($request->id);
        $this->validatePassword($request, $authUser, 'changePassword');

        $request->validate([
            'password' => ['required', 'string', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ]);

        $user->password = Hash::make($request['password']);
        $user->save();
        return back();
    }

    public function validatePassword(Request $request, $user, $errorBag)
    {
        Validator::make($request->all(), [
            'confirm_password' => ['required', 'string'],
        ])->after(function ($validator) use ($request, $user) {
            if (!isset($request['confirm_password']) || !Hash::check($request['confirm_password'], $user->password)) {
                $validator->errors()->add('confirm_password', __('The provided password does not match your current password.'));
            }
        })->validateWithBag($errorBag);
    }

    public function orderList()
    {
        return response()->json([
            'sortedUsers' => User::orderBy('sort_order')->get(),
        ]);
    }


    public function orderUpdate(Request $request)
    {
        Log::info($request->sorted);
        foreach ($request->sorted as $u) {
            User::where('id', $u['id'])->update([
                'sort_order' => $u['order']
            ]);
        }

        return response()->json(['status' => 'success']);
    }
}
