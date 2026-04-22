<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptSettingController extends Controller
{
    public function edit()
    {
        $location = Auth::user()->activeLocation;
        
        if (!$location) {
            return redirect()->route('locations.active')->with('error', __('nav.no_location_selected'));
        }

        return view('settings.receipt', compact('location'));
    }

    public function update(Request $request)
    {
        $location = Auth::user()->activeLocation;

        if (!$location) {
            return redirect()->route('locations.active')->with('error', __('nav.no_location_selected'));
        }

        $validated = $request->validate([
            'receipt_header' => 'nullable|string',
            'receipt_footer' => 'nullable|string',
            'receipt_tagline' => 'nullable|string|max:255',
        ]);

        $location->update($validated);

        return redirect()->back()->with('status', __('settings.receipt_updated'));
    }
}
