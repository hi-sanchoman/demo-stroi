<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

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
}
