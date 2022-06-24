<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

class UserController extends Controller 
{
    public function me(Request $request) {
        return User::with(['roles'])->findOrFail($request->user()->id);
    }
}