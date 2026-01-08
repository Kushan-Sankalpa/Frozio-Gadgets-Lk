<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceVariant;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Yajra\DataTables\Facades\DataTables;

class ServiceController extends Controller
{

    public function index(Request $request)
    {
        $services = Service::query()
            ->select([
                'id',
                'name',
                'description',
                'price',
                'price_type',
                'duration_minutes',
                'service_category_id',
                'sort_order',
                'status',
            ])
            ->orderBy('service_category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $grouped = $services
            ->groupBy(function ($s) {
                return (string)($s->service_category_id ?? 'uncategorized');
            })
            ->map->values();

        $counts = $services->groupBy('service_category_id')->map->count();
        $uncategorizedCount = (int) $counts->get(null, 0);

        $categories = ServiceCategory::query()
            ->select(['id', 'name', 'description', 'color_code', 'sort_order', 'status'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($c) use ($counts) {
                $c->service_count = (int) $counts->get($c->id, 0);
                $c->image_url = $c->getFirstMediaUrl('category_image') ?: null;
                return $c;
            });

        return Inertia::render('Services/Index', [
            'categories'          => $categories,
            'grouped'             => $grouped,
            'uncategorizedCount'  => $uncategorizedCount,
        ]);
    }

    public function getdata(Request $request)
    {
        $q = Service::query()->with('category:id,name');

        if ($request->filled('category_id')) {
            $q->where('service_category_id', (int)$request->category_id);
        }

        return DataTables::of($q)
            ->addColumn('check', function ($row) {
                return '<div class="custom-control custom-checkbox item-check">
                    <input type="checkbox" class="form-check-input" id="' . $row->id . '" value="' . $row->id . '">
                    <label class="form-check-label" for="' . $row->id . '"></label>
                </div>';
            })
            ->addColumn('category', fn($r) => e($r->category->name ?? '—'))
            ->editColumn('price', fn($r) => number_format((float)$r->price, 2))
            ->addColumn('duration', fn($r) => (int)$r->duration_minutes)
            ->addColumn('status', fn($r) => $this->badge($r->status))
            ->editColumn('created_at', fn($r) => optional($r->created_at)->toDateString())
            ->addColumn('action', function ($r) {
                $html = '';
                if (auth()->user()->can('service.view') || auth()->user()->can('service.edit')) {
                    $html .= '<a class="dropdown-item action_edit" style="font-size:14px;padding:5px 13px;" data-item-id="' . $r->id . '" href="javascript:void(0)">
                        <i class="fas fa-edit me-2"></i> View / Edit
                    </a>';
                }
                if (auth()->user()->can('service.edit')) {
                    $toggleLabel = ($r->status === 'active' ? ' Deactivate' : ' Activate');
                    $toggleClass = ($r->status === 'active' ? 'text-warning' : 'text-success');
                    $html .= '<a class="dropdown-item ' . $toggleClass . ' action_status_change" style="font-size:14px;padding:5px 13px;" data-item-id="' . $r->id . '" data-status="' . $r->status . '" href="javascript:void(0)">
                        <i class="fas fa-power-off me-2"></i>' . $toggleLabel . '
                    </a>';
                }
                $html .= '<div class="dropdown-divider"></div>';
                if (auth()->user()->can('service.delete')) {
                    $html .= '<a class="dropdown-item text-danger action_delete" data-bs-toggle="modal" data-bs-target="#deleteConfirm" style="font-size:14px;padding:5px 13px;" data-item-id="' . $r->id . '" href="javascript:void(0)">
                        <i class="fas fa-trash me-2"></i> Delete
                    </a>';
                }
                return '<div class="btn-group">
                    <button type="button" class="btn btn-main btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Action</button>
                    <div class="dropdown-menu" style="min-width:10rem;">' . $html . '</div>
                </div>';
            })

            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');
                if (!$search) return;

                $s = trim($search);

                $query->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                        ->orWhere('description', 'like', "%{$s}%")
                        ->orWhere('price_type', 'like', "%{$s}%");
                })->orWhereHas('category', function ($cat) use ($s) {
                    $cat->where('name', 'like', "%{$s}%");
                });

                $l = Str::lower($s);
                if (Str::contains($l, 'active')) {
                    $query->orWhere('status', 'active');
                } elseif (Str::contains($l, 'inactive')) {
                    $query->orWhere('status', 'inactive');
                }
            }, true)

            ->rawColumns(['check', 'status', 'action'])
            ->make(true);
    }

    
    /**
     * Create service form.
     */
    public function create()
    {

        $staff = User::all();
        return Inertia::render('Services/CreateUpdate', [
            'service'    => null,
            'categories' => ServiceCategory::orderBy('name')->where('status', 'active')->get(['id', 'name', 'color_code']),
            'staff' => $staff
        ]);
    }

    /**
     * Store service.
     */
    public function store(Request $request)
    {
        $this->validateService($request);

        try {
            DB::beginTransaction();

            $svc = new Service();
            $svc->service_category_id = $request->service_category_id;
            $svc->name                = $request->name;
            $svc->description         = $request->description;
            $svc->price_type          = $request->price_type;
            $svc->price               = (float)($request->price ?? 0);
            $svc->duration_minutes    = (int)$request->duration_minutes;
            $svc->status              = $request->status ?: 'active';
            $svc->save();

            if (is_array($request->variants)) {
                foreach ($request->variants as $v) {
                    ServiceVariant::create([
                        'service_id'       => $svc->id,
                        'name'             => $v['name'] ?? null,
                        'description'      => $v['description'] ?? null,
                        'price_type'       => $v['price_type'] ?? 'fixed',
                        'price'            => (float)($v['price'] ?? 0),
                        'duration_minutes' => (int)($v['duration_minutes'] ?? $svc->duration_minutes),
                    ]);
                }
            }


            $svc->user()->sync($request->staff ?? []);

            DB::commit();
            return redirect()->route('service.index');
        } catch (Exception $ex) {
            dd($ex);
            DB::rollBack();
            Log::error($ex);
            return abort(500);
        }
    }


    public function edit($id)
    {

        $staff = User::all();
        $service = Service::with('variants', 'user')->findOrFail($id);

        return Inertia::render('Services/CreateUpdate', [
            'service'    => $service,
            'categories' => ServiceCategory::orderBy('name')->get(['id', 'name', 'color_code']),

            'staff' => $staff
        ]);
    }

    /**
     * Update service.
     */
    public function update(Request $request)
    {
        $request->validate(['id' => ['required', 'exists:services,id']]);
        $this->validateService($request);

        try {
            DB::beginTransaction();

            $svc = Service::findOrFail($request->id);
            $svc->service_category_id = $request->service_category_id;
            $svc->name                = $request->name;
            $svc->description         = $request->description;
            $svc->price_type          = $request->price_type;
            $svc->price               = (float)($request->price ?? 0);
            $svc->duration_minutes    = (int)$request->duration_minutes;
            $svc->status              = $request->status ?: $svc->status;
            $svc->save();


            $svc->user()->sync($request->staff ?? []);

            DB::commit();
            return redirect()->route('service.index');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
            return abort(500);
        }
    }

    /**
     * Delete service (+ soft delete variants).
     */
    public function destroy(Request $request)
    {
        $request->validate(['id' => ['required', 'exists:services,id']]);

        try {
            DB::beginTransaction();

            $id = (int) $request->id;

            ServiceVariant::where('service_id', $id)->forceDelete();

            Service::where('id', $id)->forceDelete();

            DB::commit();

            return redirect()->route('service.index');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
            return abort(500);
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id'     => ['required', 'exists:services,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        try {
            $svc = Service::findOrFail($request->id);
            $svc->status = $request->status === 'active' ? 'inactive' : 'active';
            $svc->save();

            return redirect()->route('service.index');
        } catch (Exception $ex) {
            Log::error($ex);
            return abort(500);
        }
    }


    public function categoryGetdata(Request $request)
    {
        $q = ServiceCategory::query();

        return DataTables::of($q)
            ->editColumn('created_at', fn($r) => optional($r->created_at)->toDateString())
            ->addColumn('status', fn($r) => $this->badge($r->status))
            ->addColumn('action', function ($r) {
                $html = '';
                if (auth()->user()->can('service.create') || auth()->user()->can('service.edit')) {
                    $html .= '<a class="dropdown-item action_cat_edit" style="font-size:14px;padding:5px 13px;" data-item-id="' . $r->id . '" href="javascript:void(0)">
                        <i class="fas fa-edit me-2"></i> Edit
                    </a>';
                }
                if (auth()->user()->can('service.edit')) {
                    $toggleLabel = ($r->status === 'active' ? ' Deactivate' : ' Activate');
                    $toggleClass = ($r->status === 'active' ? 'text-warning' : 'text-success');
                    $html .= '<a class="dropdown-item ' . $toggleClass . ' action_cat_status_change" style="font-size:14px;padding:5px 13px;" data-item-id="' . $r->id . '" data-status="' . $r->status . '" href="javascript:void(0)">
                        <i class="fas fa-power-off me-2"></i>' . $toggleLabel . '
                    </a>';
                }
                $html .= '<div class="dropdown-divider"></div>';
                if (auth()->user()->can('service.delete')) {
                    $html .= '<a class="dropdown-item text-danger action_cat_delete" data-bs-toggle="modal" data-bs-target="#deleteConfirm" style="font-size:14px;padding:5px 13px;" data-item-id="' . $r->id . '" href="javascript:void(0)">
                        <i class="fas fa-trash me-2"></i> Delete
                    </a>';
                }
                return '<div class="btn-group">
                    <button type="button" class="btn btn-main btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Action</button>
                    <div class="dropdown-menu" style="min-width:10rem;">' . $html . '</div>
                </div>';
            })

            // 🔎 search
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');
                if (!$search) return;

                $s = trim($search);
                $query->where('name', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%");

                $l = Str::lower($s);
                if (Str::contains($l, 'active')) {
                    $query->orWhere('status', 'active');
                } elseif (Str::contains($l, 'inactive')) {
                    $query->orWhere('status', 'inactive');
                }
            }, true)

            ->rawColumns(['status', 'action'])
            ->make(true);
    }


    public function categoryStore(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'color_code'  => ['nullable', 'string', 'max:16'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        try {
            DB::beginTransaction();

            $cat = ServiceCategory::create([
                'name'        => $request->name,
                'color_code'  => $request->color_code,
                'description' => $request->description,
                'status'      => 'active',
            ]);

            if ($request->hasFile('image')) {
                $cat->addMediaFromRequest('image')->toMediaCollection('category_image');
            }

            DB::commit();
            return redirect()->route('service.index');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
            return abort(500);
        }
    }

    public function categoryUpdate(Request $request)
    {
        $request->validate([
            'id'          => ['required', 'exists:service_categories,id'],
            'name'        => ['required', 'string', 'max:255'],
            'color_code'  => ['nullable', 'string', 'max:16'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status'      => ['nullable', Rule::in(['active', 'inactive'])],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        try {
            DB::beginTransaction();

            $cat = ServiceCategory::findOrFail($request->id);
            $cat->name        = $request->name;
            $cat->color_code  = $request->color_code;
            $cat->description = $request->description;
            $cat->status      = $request->status ?: $cat->status;
            $cat->save();

            if ($request->hasFile('image')) {

                $cat->addMediaFromRequest('image')->toMediaCollection('category_image');
            }

            DB::commit();
            return redirect()->route('service.index');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
            return abort(500);
        }
    }


    public function categoryDestroy(Request $request)
    {
        $data = $request->validate([
            'id'         => ['required', 'integer', 'exists:service_categories,id'],
            'delete_all' => ['sometimes', 'boolean'],
        ]);

        $categoryId = (int) $data['id'];
        $deleteAll  = (bool) ($data['delete_all'] ?? false);

        try {
            DB::beginTransaction();

            if ($deleteAll) {
                $serviceIds = Service::where('service_category_id', $categoryId)
                    ->pluck('id');

                if ($serviceIds->isNotEmpty()) {
                    ServiceVariant::whereIn('service_id', $serviceIds)->forceDelete();
                    Service::whereIn('id', $serviceIds)->forceDelete();
                }
            } else {
                Service::where('service_category_id', $categoryId)
                    ->update(['service_category_id' => null]);
            }

            ServiceCategory::where('id', $categoryId)->forceDelete();

            DB::commit();

            return redirect()->route('service.index');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
            return abort(500);
        }
    }



    public function categoryStatusUpdate(Request $request)
    {
        $request->validate([
            'id'     => ['required', 'exists:service_categories,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        try {
            $cat = ServiceCategory::findOrFail($request->id);
            $cat->status = $request->status === 'active' ? 'inactive' : 'active';
            $cat->save();

            return redirect()->route('service.index');
        } catch (Exception $ex) {
            Log::error($ex);
            return abort(500);
        }
    }


    public function variantGetdata(Request $request, int $serviceId)
    {
        $q = ServiceVariant::query()->where('service_id', $serviceId);

        return DataTables::of($q)
            ->editColumn('created_at', fn($r) => optional($r->created_at)->toDateString())
            ->addColumn('price', fn($r) => number_format((float)$r->price, 2))
            ->addColumn('duration', fn($r) => (int)$r->duration_minutes)
            ->addColumn('action', function ($r) {
                $html = '';
                if (auth()->user()->can('service.edit')) {
                    $html .= '<a class="dropdown-item action_var_edit" style="font-size:14px;padding:5px 13px;" data-item-id="' . $r->id . '" href="javascript:void(0)">
                        <i class="fas fa-edit me-2"></i> Edit
                    </a>';
                }
                if (auth()->user()->can('service.delete')) {
                    $html .= '<a class="dropdown-item text-danger action_var_delete" data-bs-toggle="modal" data-bs-target="#deleteConfirm" style="font-size:14px;padding:5px 13px;" data-item-id="' . $r->id . '" href="javascript:void(0)">
                        <i class="fas fa-trash me-2"></i> Delete
                    </a>';
                }
                return '<div class="btn-group">
                    <button type="button" class="btn btn-main btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Action</button>
                    <div class="dropdown-menu" style="min-width:10rem;">' . $html . '</div>
                </div>';
            })

            // 🔎 search
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');
                if (!$search) return;

                $s = trim($search);
                $query->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                        ->orWhere('description', 'like', "%{$s}%")
                        ->orWhere('price_type', 'like', "%{$s}%");
                });
            }, true)

            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Store variant (for the "Add variant" popup).
     */
    // ServiceController.php
    public function variantStore(Request $request)
    {
        $request->validate([
            'service_id'       => ['required', 'integer', 'exists:services,id'],
            'name'             => ['nullable', 'string', 'max:50'],
            'description'      => ['nullable', 'string', 'max:200'],
            'price_type'       => ['required', Rule::in(['fixed', 'from', 'free'])],
            'price'            => ['nullable', 'numeric', 'min:0'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();

            ServiceVariant::create([
                'service_id'       => (int)$request->service_id,
                'name'             => $request->name,
                'description'      => $request->description,
                'price_type'       => $request->price_type,
                'price'            => (float)($request->price ?? 0),
                'duration_minutes' => (int)$request->duration_minutes,
            ]);

            DB::commit();
            return back();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
            return abort(500);
        }
    }


    
    public function variantUpdate(Request $request)
    {
        $request->validate([
            'id'               => ['required', 'exists:service_variants,id'],
            'name'             => ['nullable', 'string', 'max:50'],
            'description'      => ['nullable', 'string', 'max:200'],
            'price_type'       => ['required', Rule::in(['fixed', 'from', 'free'])],
            'price'            => ['nullable', 'numeric', 'min:0'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();

            $v = ServiceVariant::findOrFail($request->id);
            $v->name             = $request->name;
            $v->description      = $request->description;
            $v->price_type       = $request->price_type;
            $v->price            = (float)($request->price ?? 0);
            $v->duration_minutes = (int)$request->duration_minutes;
            $v->save();

            DB::commit();
            return back();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
            return abort(500);
        }
    }

    /**
     * Delete variant (soft).
     */
    public function variantDestroy(Request $request)
    {
        $request->validate(['id' => ['required', 'exists:service_variants,id']]);

        try {
            ServiceVariant::where('id', (int)$request->id)->forceDelete();
            return back();
        } catch (Exception $ex) {
            Log::error($ex);
            return abort(500);
        }
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    protected function validateService(Request $request): void
    {
        $request->validate([
            'service_category_id' => ['nullable', 'integer', 'exists:service_categories,id'],
            'name'                => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:5000'],
            'price_type'          => ['required', Rule::in(['fixed', 'from', 'free'])],
            'price'               => ['nullable', 'numeric', 'min:0'],
            'duration_minutes'    => ['required', 'integer', 'min:1'],
            'status'              => ['nullable', Rule::in(['active', 'inactive'])],
        ], [], [
            'service_category_id' => 'category',
            'duration_minutes'    => 'duration',
        ]);
    }

    protected function badge(?string $status): string
    {
        $s = $status === 'active' ? 'active' : 'inactive';
        if ($s === 'active') {
            return '<span class="badge bg-success">Active</span>';
        }
        return '<span class="badge bg-warning">Inactive</span>';
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'categories' => ['array'],
            'categories.*.id' => ['integer', 'exists:service_categories,id'],
            'categories.*.sort_order' => ['integer', 'min:0'],

            'services' => ['required', 'array'],
            'services.*.id' => ['integer', 'exists:services,id'],
            'services.*.service_category_id' => ['nullable', 'integer', 'exists:service_categories,id'],
            'services.*.sort_order' => ['integer', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            if (!empty($data['categories'])) {
                foreach ($data['categories'] as $c) {
                    ServiceCategory::where('id', $c['id'])->update([
                        'sort_order' => $c['sort_order'] ?? 0,
                    ]);
                }
            }

            foreach ($data['services'] as $s) {
                Service::where('id', $s['id'])->update([
                    'service_category_id' => $s['service_category_id'] ?? null,
                    'sort_order' => $s['sort_order'] ?? 0,
                ]);
            }
        });

        return redirect()->route('service.index');
    }
}
