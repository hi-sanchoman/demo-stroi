<?php

namespace App\Http\Controllers;

use App\Exports\ApplicationExport;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\ApplicationOffer;
use App\Models\EquipmentOffer;
use App\Models\Product;
use App\Models\ServiceOffer;
use Maatwebsite\Excel\Excel;
use Storage;

class ApplicationController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $applications = Application::get();

        return view('applications.index', compact('applications'));
    }

    public function create()
    {
        return view('applications.index');
    }

    public function edit()
    {
        return view('applications.index');
    }


    public function uploadFile(Request $request)
    {
        $file = request()->file('file');

        $path = time() . '.' . $file->getClientOriginalExtension();

        $file->move(public_path('/uploads'), $path);

        if ($request->kind == 'product') {
            $offer = ApplicationOffer::where('id', $request->offer_id)->firstOrFail();
            $offer->file = $path;
            $offer->save();
        } else if ($request->kind == 'service') {
            $offer = ServiceOffer::where('id', $request->offer_id)->firstOrFail();
            $offer->file = $path;
            $offer->save();
        } else if ($request->kind == 'equipment') {
            $offer = EquipmentOffer::where('id', $request->offer_id)->firstOrFail();
            $offer->file = $path;
            $offer->save();
        }

        return [
            'data' => [
                'file' => $path,
            ],
        ];
    }


    public function parse()
    {
        return;
        $file = public_path('goods.tsv');
        $lines = collect([]);

        $handle = fopen($file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $lines->push(trim(preg_replace('/\s\s+/', ' ', $line)));
            }

            fclose($handle);
        }

        // dd($lines->unique());
        // Product::truncate();

        $data = [];

        foreach ($lines->unique() as $line) {
            $data[] = [
                'name' => $line,
                'price' => 1,
            ];
        }

        Product::insert($data);

        return 'done';
    }

    public function exportApplication($id) {
        $application = Application::query()
            ->with(['construction', 'applicationApplicationProducts'])
            ->find($id);

        return (new ApplicationExport($application))->download('Заявка #' . $application->id . '.xlsx');
    }
}
