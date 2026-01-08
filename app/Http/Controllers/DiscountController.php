<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceCategory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Yajra\DataTables\Facades\DataTables;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $discounts = Discount::all();
        return Inertia::render('Discounts/Index', ['discounts' => $discounts]);
    }
    public function getData()
    {

        $logged_user = Auth::user();

        if ($logged_user->client) {
            $discounts = Discount::where(['tenant_id' => $logged_user->client->tenant_id]);
            $currency = Currency::where(['tenant_id' => $logged_user->client->tenant_id])->first();
        } else {
            $discounts = collect([]);
            $currency = collect([]);
        }
        return DataTables::of($discounts)
            ->addColumn('check', function ($row) {
                return '<div class="custom-control custom-checkbox item-check">
            <input type="checkbox" class="form-check-input" id="' . $row->id . '" value="' . $row->id . '">
            <label class="form-check-label form-check-label" for="' . $row->id . '"></label>
          </div>';
            })
            ->addColumn('action', function ($row) {
                $action_html = '';
                if (auth()->user()->can('brands.view') && auth()->user()->can('brands.edit')) {
                    $action_html .= '<Button class="btn btn-outline-secondary btn-lg me-2 action_edit" data-item-id="' . $row->id . '"><i data-item-id="' . $row->id . '" class="fa fa-pencil-square-o"></i></Button>';
                }
                if (auth()->user()->can('brands.delete')) {
                    $action_html .= '<Button data-bs-toggle="modal" data-bs-target="#deleteConfirm"  data-item-id="' . $row->id . '" class="btn btn-outline-danger btn-lg me-2 action_delete"><i data-item-id="' . $row->id . '" class="fa fa-trash-o"></i></Button>';
                }
                return '<div class="btn-group">'
                    . $action_html .
                    '</div>';
            })

            ->addColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge bg-success">Active</span>';
                } else if ($row->status == 0) {
                    return '<span class="badge bg-warning">Inactive</span>';
                }
            })
            ->addColumn('discount_type', function ($row) {
                if ($row->discount_type == 'fixed') {
                    return '<span class="badge bg-info">Fixed</span>';
                } else if ($row->discount_type == 'percentage') {
                    return '<span class="badge bg-info">Percentage</span>';
                }
            })
            ->addColumn('discount_amount', function ($row) use ($currency) {
                if ($row->discount_type == 'fixed') {
                    return $row->discount_amount . '(' . $currency->symbol . ')';
                } else if ($row->discount_type == 'percentage') {
                    return $row->discount_amount . '(%)';
                }
            })
            ->rawColumns(['check', 'action', 'status', 'discount_type'])
            ->make(true);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ServiceCategory::all();
        $services = Service::all();

        return Inertia::render('Discounts/CreateUpdate', ['categories' => $categories, 'services' => $services]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
     $request->validate(
        [
            'name'            => 'required|string',
            'description'     => 'nullable|string',
            'discount_type'   => 'required|in:fixed,percentage',
            'discount_amount' => 'required|numeric',
            'priority'        => 'required|integer',
            'starts_at'       => 'required|date',
            'ends_at'         => 'required|date|after_or_equal:starts_at',
            'status'          => 'required|boolean',
            'services'        => 'required|array',
            'services.*'      => 'exists:services,id',

            'discount_image'  => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ],
        [
            'discount_image.mimes' => 'The image must be a JPG or PNG file. WEBP images are not supported.',
            'discount_image.max'   => 'The image may not be greater than 2 MB.',
        ]
    );
        DB::beginTransaction();
        try {

            $discount = new Discount();
            $discount->name = $request->name;
            $discount->description = $request->description;
            $discount->discount_type = $request->discount_type;
            $discount->discount_amount = $request->discount_amount;
            $discount->priority = $request->priority;
            $discount->starts_at = $request->starts_at;
            $discount->ends_at = $request->ends_at;
            $discount->status = $request->status;
            $discount->save();

            if ($request->services && is_array($request->services)) {
                $discount->services()->attach($request->services);
            }

            if ($request->hasFile('discount_image')) {
                $discount->addMediaFromRequest('discount_image')
                    ->toMediaCollection('discount_image');
            }

            DB::commit();

            return redirect()->route('discounts');
        } catch (Exception $ex) {
            dd($ex);
            Log::error($ex);
            DB::rollBack();
            abort(500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Discount $discount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $discount = Discount::with('services')->find($id);
        $categories = ServiceCategory::all();
        $services = Service::all();

        // dd( $discount);
        return Inertia::render('Discounts/CreateUpdate', ['discount' => $discount, 'categories' => $categories, 'services' => $services]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // dd($request->all());
       $request->validate(
        [
            'name'            => 'required|string',
            'description'     => 'nullable|string',
            'discount_type'   => 'required|in:fixed,percentage',
            'discount_amount' => 'required|numeric',
            'priority'        => 'required|integer',
            'starts_at'       => 'required|date',
            'ends_at'         => 'required|date|after_or_equal:starts_at',
            'status'          => 'required|boolean',
            'discount_image'  => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ],
        [
            'discount_image.mimes' => 'The image must be a JPG or PNG file. WEBP images are not supported.',
            'discount_image.max'   => 'The image may not be greater than 2 MB.',
        ]
    );
        DB::beginTransaction();
        try {

            $discount = Discount::find($request->id);

            $discount->name = $request->name;
            $discount->description = $request->description;
            $discount->discount_type = $request->discount_type;
            $discount->discount_amount = $request->discount_amount;
            $discount->priority = $request->priority;
            $discount->starts_at = $request->starts_at;
            $discount->ends_at = $request->ends_at;
            $discount->status = $request->status;
            $discount->save();

            if ($request->services && is_array($request->services)) {
                $discount->services()->sync($request->services);
            } else {
                $discount->services()->sync([]);
            }

            if ($request->hasFile('discount_image')) {
                $discount->clearMediaCollection('discount_image');
                $discount->addMediaFromRequest('discount_image')
                    ->toMediaCollection('discount_image');
            }

            DB::commit();

            return redirect()->route('discounts');
        } catch (Exception $ex) {
            // dd($ex);
            Log::error($ex);
            DB::rollBack();
            abort(500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        try {
            $discount = Discount::findOrFail($id);

            $discount->status = $request->status;
            $discount->save();

            return redirect()->back()->with('success', 'Status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update status.');
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $discount = Discount::findOrFail($id);

            // Delete pivot (services)
            $discount->services()->detach();

            $discount->delete();

            return redirect()->route('discounts');
        } catch (Exception $ex) {

            // dd($ex);
            DB::rollback();
            Log::error($ex);
            return abort(500);
        }
    }

   public function select(Request $request)
{
    $data = $request->validate([
        'branch_id'  => ['nullable', 'integer'],
        'service_id' => ['nullable', 'integer'],
        'start'      => ['nullable', 'date'],
        'end'        => ['nullable', 'date'],
    ]);

    try {
        $start = !empty($data['start']) ? Carbon::parse($data['start']) : null;
        $end   = !empty($data['end'])   ? Carbon::parse($data['end'])   : null;
    } catch (\Throwable $e) {
        return response()->json(['message' => 'Invalid date range'], 422);
    }

    $q = Discount::query()->where('is_active', 1);

    if ($start && $end) {
        $q->where(function ($qq) use ($start, $end) {
            $qq->where(function ($q1) use ($end) {
                $q1->whereNull('starts_at')->orWhere('starts_at', '<=', $end);
            })->where(function ($q2) use ($start) {
                $q2->whereNull('ends_at')->orWhere('ends_at', '>=', $start);
            });
        });
    }

    if (!empty($data['branch_id'])) {
        $q->where(function ($qq) use ($data) {
            $qq->whereNull('branch_id')->orWhere('branch_id', $data['branch_id']);
        });
    }

    if (!empty($data['service_id'])) {
        $q->where(function ($qq) use ($data) {
            $qq->whereNull('service_id')->orWhere('service_id', $data['service_id']);
        });
    }

    $discounts = $q->orderBy('name')->get(['id','name','type','value']);

    return response()->json([
        'data' => $discounts->map(fn($d) => [
            'id'    => $d->id,
            'name'  => $d->name,
            'type'  => $d->type,
            'value' => (float) $d->value,
        ])->values(),
    ]);
}
}
