<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BannerController extends Controller
{
    public $pagename = 'Banners';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::all();

        return Inertia::render('Banners/Index', [
            'banners' => $banners,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Banners/CreateUpdate');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'banner_name'  => 'required|string|max:255',
            'banner_image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'banner_description' => 'nullable|string|max:2000', 
        ]);

        try {
            DB::beginTransaction();

            $banner = new Banner();
            $banner->banner_name = $request->banner_name;
            $banner->banner_description = $request->banner_description;
            $banner->status = 1;
            $banner->save();

            if ($request->hasFile('banner_image')) {
                $banner->addMediaFromRequest('banner_image')
                    ->toMediaCollection('banner_image');
            }

            DB::commit();

            return redirect()->route('banner.index');
        } catch (Exception $ex) {
            DB::rollBack();
            // optional: log error
            return abort(500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $banner = Banner::findOrFail($id);

        return Inertia::render('Banners/CreateUpdate', [
            'banner' => $banner,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'banner_name'  => 'required|string|max:255',
            'banner_image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'banner_description' => 'nullable|string|max:2000',
        ]);

        try {
            DB::beginTransaction();

            $banner = Banner::with('media')->findOrFail($request->id);
            $banner->banner_name = $request->banner_name;
            $banner->banner_description = $request->banner_description;
            $banner->save();

            if ($request->hasFile('banner_image')) {
                $banner->clearMediaCollection('banner_image');
                $banner->addMediaFromRequest('banner_image')
                    ->toMediaCollection('banner_image');
            }

            DB::commit();

            return redirect()->route('banner.index');
        } catch (Exception $ex) {
            DB::rollBack();
            return abort(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $banner = Banner::findOrFail($id);

            if (method_exists($banner, 'clearMediaCollection')) {
                $banner->clearMediaCollection('banner_image');
            }

            $banner->delete();

            DB::commit();

            return redirect()->route('banner.index');
        } catch (Exception $ex) {
            DB::rollBack();
            return abort(500);
        }
    }

    /**
     * Toggle active / inactive status.
     */
    public function toggleStatus(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->status = $request->input('status', 0);
        $banner->save();

        return back()->with('success', 'Banner status updated successfully.');
    }
}
