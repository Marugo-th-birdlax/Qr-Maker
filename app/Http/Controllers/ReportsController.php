<?php

// app/Http/Controllers/ReportsController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Part;
use App\Exports\PartsExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function index(Request $req)
    {
        $q = Part::query();

        if ($kw = trim($req->get('q',''))) {
            $q->where(function($w) use ($kw) {
                $w->where('part_no', 'like', "%$kw%")
                  ->orWhere('part_name', 'like', "%$kw%")
                  ->orWhere('supplier_name', 'like', "%$kw%")
                  ->orWhere('supplier_code', 'like', "%$kw%");
            });
        }
        if ($sc = $req->get('supplier_code')) $q->where('supplier_code', $sc);
        if ($t  = $req->get('type'))          $q->where('type', $t);
        if ($s  = $req->get('supplier'))      $q->where('supplier', $s);   // กลุ่ม
        if ($l  = $req->get('location'))      $q->where('location', $l);

        $perPage = (int)($req->get('per_page', 50)) ?: 50;
        $parts   = $q->orderBy('part_no')->paginate($perPage)->withQueryString();

        // dropdown options
        $supplierCodes = Part::select('supplier_code')->distinct()->pluck('supplier_code')->filter()->values();
        $types         = Part::select('type')->distinct()->pluck('type')->filter()->values();
        $suppliers     = Part::select('supplier')->distinct()->pluck('supplier')->filter()->values();
        $locations     = Part::select('location')->distinct()->pluck('location')->filter()->values();

        return view('reports.index', compact('parts','supplierCodes','types','suppliers','locations'));
    }

    public function exportXlsx(Request $req)
    {
        $filters = $req->only(['q','supplier_code','type','supplier','location']);
        $filename = 'parts_report_'.now()->format('Ymd_His').'.xlsx';
        return (new PartsExport($filters))->download($filename); // XLSX ตามนามสกุลไฟล์
    }
}
