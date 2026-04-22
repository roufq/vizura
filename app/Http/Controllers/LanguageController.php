<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch language and store in session.
     */
    public function update($locale)
    {
        if (in_array($locale, ['en', 'id'])) {
            Session::put('locale', $locale);
        }

        return redirect()->back();
    }
}
