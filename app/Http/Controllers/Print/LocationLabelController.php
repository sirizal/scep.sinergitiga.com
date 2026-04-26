<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationLabelController extends Controller
{
    public function printSingle(Location $location)
    {
        return view('print.location-label', [
            'locations' => [$location],
        ]);
    }

    public function printBulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|string',
        ]);

        $ids = explode(',', $request->ids);
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, fn ($id) => $id > 0);

        if (empty($ids)) {
            return back()->with('error', 'No locations found.');
        }

        $locations = Location::whereIn('id', $ids)->get();

        if ($locations->isEmpty()) {
            return back()->with('error', 'No locations found.');
        }

        return view('print.location-label', [
            'locations' => $locations,
        ]);
    }
}
