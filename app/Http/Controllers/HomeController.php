<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index() {
        // Ini khusus nampilkan halaman login saja
        if(Auth::check()) {
            return Inertia::render('home/Index', [
                'user' => auth()->user()
            ]);
        }
        return Inertia::render('auth/Login');
    }

    public function dashboard() {
        // Ini khusus halaman setelah login
        return Inertia::render('home/Index', [
            'user' => auth()->user()
        ]);
    }
}
