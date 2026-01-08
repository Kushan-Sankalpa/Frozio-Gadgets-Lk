<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\LoyaltyTier;
use App\Models\Tier;
use App\Models\Tiers;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log as facedeslog;
use Inertia\Inertia;

class TierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $pagename = 'Tiers';


    public function index(Request $request)
    {
        $loyalty_tier = LoyaltyTier::with('media')->get();
        return Inertia::render('LoyaltyProgram/Index', ['loyalty_tier' => $loyalty_tier]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Tier $tiers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tier $tiers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // dd($request->all());
        try {

            DB::beginTransaction();

            $tier = LoyaltyTier::find($request->id);
            $tier->name = $request->name;
            $tier->description = $request->description;
            $tier->start_points = $request->start_points;
            $tier->end_points = $request->end_points;
            $tier->save();

            if ($request->hasFile('image')) {
                if($tier->media) {
                    Storage::disk('public')->delete($tier->media);
                    $tier->clearMediaCollection('Tiers');
                }
                $tier->addMedia($request->file('image'))->toMediaCollection('Tiers');
            }

            DB::commit();

            return redirect()->route('loyalty-program.index');
        } catch (Exception $ex) {
            dd($ex);
            DB::rollback();
            facedeslog::error($ex);
            return abort(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tier $tiers)
    {
        //
    }
}
