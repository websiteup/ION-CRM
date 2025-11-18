<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DarkModeController extends Controller
{
    public function toggle(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $darkMode = $request->input('dark_mode', false);
        $user->dark_mode = $darkMode;
        $user->save();
        
        return response()->json([
            'success' => true,
            'dark_mode' => $darkMode
        ]);
    }
}
