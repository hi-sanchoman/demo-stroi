<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\Badge;

class UserController extends Controller
{
    public function me(Request $request)
    {
        return User::with(['roles'])->findOrFail($request->user()->id);
    }

    public function getUnreadBadge(Request $request)
    {
        // dd($request->all());

        $badge = Badge::query()
            ->where('type', $request->type)
            ->where('status', 'unread')
            ->where('user_id', $request->user()->id)
            ->first();

        return $badge != null ? $badge->quantity : 0;
    }

    public function readBadge(Request $request)
    {
        $badge = Badge::query()
            ->where('type', $request->type)
            ->where('status', 'unread')
            ->where('user_id', $request->user()->id)
            ->first();

        // dd($badge); 

        if ($badge != null) {
            $badge->quantity = 0;
            $badge->save();
        }
    }
}
