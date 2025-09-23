<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Part;


class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'qr_total'       => 0,
            'qr_today'       => 0,
            'pending_export' => 0,
            'parts_total'    => Part::count(),
            'suppliers'      => Part::distinct('supplier_code')->count('supplier_code'),
            'today_import'   => Part::whereDate('created_at', today())->count(),
        ];

        $recent = Part::latest('created_at')->limit(5)->get();

        return view('home.index', compact('stats','recent'));
    }
}
