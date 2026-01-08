<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Log;
use App\Models\ServiceType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{
    public $pagename = 'Branches';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::all();
        return Inertia::render('Branches/Index', ['branches' => $branches]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return Inertia::render('Branches/CreateUpdate');
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
 'contact_number'  => [
            'nullable',
            'regex:/^[0-9]+$/',
            'max:10',
        ],            'business_located' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'opening_hours' => 'nullable|array',
            'branch_image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'branch_gallery.*' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
        ]);

        try {
            DB::beginTransaction();

            $branch = new Branch();
            $branch->name = $request->name;
            $branch->email = $request->email;
            $branch->contact_number = $request->contact_number;
            $branch->business_located = $request->business_located;
            $branch->latitude = $request->latitude;
            $branch->longitude = $request->longitude;
            $branch->company_name = $request->company_name;
            $branch->address = $request->address;
            $branch->city = $request->city;
            $branch->state = $request->state;
            $branch->postcode = $request->postcode;

            if ($request->has('opening_hours') && is_array($request->opening_hours)) {
                $normalizedHours = collect($request->opening_hours)->map(function ($item) {
                    if (is_array($item) && array_key_exists('enabled', $item)) {
                        $item['enabled'] = filter_var($item['enabled'], FILTER_VALIDATE_BOOLEAN);
                    }

                    $item['intervals'] = isset($item['intervals']) && is_array($item['intervals'])
                        ? $item['intervals']
                        : [['open' => '09:00', 'close' => '18:00']];

                    return $item;
                })->toArray();

                $branch->opening_hours = $normalizedHours;
            } else {
                $branch->opening_hours = [];
            }

            $branch->save();

            if ($request->hasFile('branch_image')) {
                $branch->addMediaFromRequest('branch_image')
                    ->toMediaCollection('branch_image');
            }

            if ($request->hasFile('branch_gallery')) {

                foreach ($request->file('branch_gallery') as $index => $file) {

                    $branch->addMedia($file)
                        ->withCustomProperties([
                            'position' => $index,
                            'type' => 'gallery_image'
                        ])
                        ->toMediaCollection('branch_gallery');
                }
            }

            DB::commit();


            return redirect()->route('branch.index');
        } catch (Exception $ex) {
            dd($ex);
            DB::rollback();
            return abort(500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $branch = Branch::find($id);
        return Inertia::render('Branches/CreateUpdate', ['branch' => $branch]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // dd($request->all());
        try {
            DB::beginTransaction();

            $branch = Branch::with('media')->find($request->id);
            $branch->name = $request->name;
            $branch->email = $request->email;
            $branch->contact_number = $request->contact_number;
            $branch->business_located = $request->business_located;
            $branch->latitude = $request->latitude;
            $branch->longitude = $request->longitude;
            $branch->company_name = $request->company_name;
            $branch->address = $request->address;
            $branch->city = $request->city;
            $branch->state = $request->state;
            $branch->postcode = $request->postcode;

            if ($request->has('opening_hours') && is_array($request->opening_hours)) {
                $normalizedHours = collect($request->opening_hours)->map(function ($item) {
                    if (is_array($item) && array_key_exists('enabled', $item)) {

                        $item['enabled'] = filter_var($item['enabled'], FILTER_VALIDATE_BOOLEAN);
                    }

                    $item['intervals'] = isset($item['intervals']) && is_array($item['intervals'])
                        ? $item['intervals']
                        : [['open' => '09:00', 'close' => '18:00']];

                    return $item;
                })->toArray();

                $branch->opening_hours = $normalizedHours;
            } else {
                $branch->opening_hours = [];
            }

            $branch->save();


            if ($request->hasFile('branch_image')) {
                $branch->clearMediaCollection('branch_image');
                $branch->addMediaFromRequest('branch_image')
                    ->toMediaCollection('branch_image');
            }

            if ($request->removedInitial && is_array($request->removedInitial)) {
                foreach ($branch->getMedia('branch_gallery') as $media) {
                    if (in_array($media->original_url, $request->removedInitial)) {
                        $media->delete();
                    }
                }
            }

            if ($request->hasFile('branch_gallery')) {
                foreach ($request->file('branch_gallery') as $index => $file) {
                    $branch->addMedia($file)
                        ->withCustomProperties([
                            'position' => $index,
                            'type' => 'gallery',
                        ])
                        ->toMediaCollection('branch_gallery');
                }
            }

            DB::commit();


            return redirect()->route('branch.index');
        } catch (Exception $ex) {
            dd($ex);
            DB::rollback();
            return abort(500);
        }
    }

    public function updateStatus(Request $request)
    {
        // dd($request->all());
        try {
            $branch = Branch::find($request->id);
            if ($request->status == 0) {
                $branch->status = 1;
            } else {
                $branch->status = 0;
            }
            $branch->save();

            $logEntry = new Log();
            $logEntry->u_id = Auth::user()->email;
            $logEntry->page = $this->pagename;
            $logEntry->function = 'UPDATE STATUS';
            $logEntry->details = 'Complete';
            $logEntry->save();

            return redirect()->route('branch.index');
        } catch (Exception $ex) {
            DB::rollback();
            DB::beginTransaction();
            $logEntry = new Log();
            $logEntry->u_id = Auth::user()->email;
            $logEntry->page = $this->pagename;
            $logEntry->function = 'UPDATE STATUS';
            $logEntry->details = $ex->getMessage();
            $logEntry->save();
            DB::commit();
            Log::error($ex);
            return abort(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $branch = Branch::findOrFail($id);

            // If branch has media (optional)
            if (method_exists($branch, 'clearMediaCollection')) {
                $branch->clearMediaCollection(); // for spatie/media-library
            }

            $branch->delete();


            return redirect()->route('branch.index');
        } catch (Exception $ex) {

            DB::rollback();
            return abort(500);
        }
    }

    public static function getBranch()
    {

        if (session()->has('branch_id')) {
            $branch = Branch::find(session('branch_id'));
        } else if (Auth::user()->branch) {
            $branch = Branch::find(Auth::user()->branch);
        } else {
            $branch = null;
        }

        return $branch;
    }

    public function setBackendBranch(Request $request)
    {
        $request->session()->put('branch_id', $request->id);
        // dd(session()->all());
        return redirect()->back();
    }

    public function toggleStatus(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $branch->status = $request->input('status', 0);
        $branch->save();

        return back()->with('success', 'Branch status updated successfully.');
    }
}
