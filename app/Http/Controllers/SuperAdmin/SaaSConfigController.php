<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SaasConfig;
use Illuminate\Http\Request;

class SaaSConfigController extends Controller
{
    public function index()
    {
        $configs = [
            'upgrade_pstore_url' => SaasConfig::get('upgrade_pstore_url'),
            'upgrade_wa_number' => SaasConfig::get('upgrade_wa_number'),
            'upgrade_email' => SaasConfig::get('upgrade_email'),
            'upgrade_message' => SaasConfig::get('upgrade_message'),
        ];
        return view('super-admin.settings', compact('configs'));
    }

    public function update(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            SaasConfig::set($key, $value);
        }

        return back()->with('status', 'SaaS Settings updated successfully!');
    }
}
