<?php

// app/Exports/PartsExport.php
namespace App\Exports;

use App\Models\Part;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\{FromQuery,WithHeadings,WithMapping,ShouldAutoSize,Exportable};

class PartsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(private array $filters = []) {}

    public function query()
    : Builder {
        $q = Part::query();

        if ($kw = trim($this->filters['q'] ?? '')) {
            $q->where(fn($w)=>$w->where('part_no','like',"%$kw%")
                ->orWhere('part_name','like',"%$kw%")
                ->orWhere('supplier_name','like',"%$kw%")
                ->orWhere('supplier_code','like',"%$kw%"));
        }
        if ($sc = ($this->filters['supplier_code'] ?? null)) $q->where('supplier_code',$sc);
        if ($t  = ($this->filters['type'] ?? null))          $q->where('type',$t);
        if ($s  = ($this->filters['supplier'] ?? null))      $q->where('supplier',$s);
        if ($l  = ($this->filters['location'] ?? null))      $q->where('location',$l);

        return $q->orderBy('part_no');
    }

    public function headings(): array
    {
        return [
            'No','PIC','TYPE','SUPPLIER(group)','Supplier code','Supplier Name',
            'Location','Part No','PART NAME',"Q'ty /Box",'MOQ (Pcs)','Item No.','UNIT','Remark','Date (YYYY-MM-DD)'
        ];
    }

    public function map($p): array
    {
        return [
            $p->no,
            $p->pic,
            $p->type,
            $p->supplier,
            $p->supplier_code,
            $p->supplier_name,
            $p->location,
            $p->part_no,
            $p->part_name,
            $p->qty_per_box,
            $p->moq,
            $p->item_no,
            $p->unit,
            $p->remark,
            optional($p->date)->format('Y-m-d'),
        ];
    }
}
