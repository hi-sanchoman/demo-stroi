<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\ApplicationOffer;
use App\Models\EquipmentOffer;
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


    public function uploadFile(Request $request) {
        $file = request()->file('file');

        $path = time() . '.' . $file->getClientOriginalExtension();

        $file->move(public_path('/uploads'), $path);

        if ($request->kind == 'product') {
            $offer = ApplicationOffer::where('id', $request->offer_id)->firstOrFail();
            $offer->file = $path;
            $offer->save();
        }

        else if ($request->kind == 'equipment') {
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
    
}
