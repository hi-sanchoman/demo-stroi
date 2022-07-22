<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\ApplicationOpenedStatus;
use App\Models\Badge;

class UserController extends Controller
{
    public function me(Request $request)
    {
        return User::with(['roles'])->findOrFail($request->user()->id);
    }

    public function saveDeviceToken(Request $request)
    {
        $request->user()->device_token = $request->device_token;
        $request->user()->save();

        return 1;
    }

    public function getUnreadBadge(Request $request)
    {
        // dd($request->all());

        // $badge = Badge::query()
        //     ->where('type', $request->type)
        //     ->where('status', 'unread')
        //     ->where('user_id', $request->user()->id)
        //     ->first();
        // return $badge != null ? $badge->quantity : 0;

        $unread = ApplicationOpenedStatus::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'unread')
            ->count();
        return $unread;
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

    public function updateProfile(Request $request)
    {
        if ($request->has('password')) {
            // update password
        }

        $user = $request->user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return 'ok';
    }
    

    public function uploadPhoto(Request $request) 
    {        
        $file = request()->file('file');

        $path = time() . '.' . $file->getClientOriginalExtension();

        $file->move(public_path('/uploads'), $path);

        $photoUrl = '/uploads/' . $path;

        $user = $request->user();
        $user->photo_url = $photoUrl;
        $user->save();

        return [
            'data' => [
                'photo' => $photoUrl,
            ],
        ];
    }
}
