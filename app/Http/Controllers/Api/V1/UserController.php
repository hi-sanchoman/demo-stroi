<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\ApplicationOpenedStatus;
use App\Models\Badge;
use App\Models\ContractOpenedStatus;

class UserController extends Controller
{
    public function me(Request $request)
    {
        return User::with(['roles'])->findOrFail($request->user()->id);
    }

    public function index()
    {
        return User::with(['roles'])->orderBy('name')->get();
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

        if ($request->type == 'applications') {
            $badgesDb = ApplicationOpenedStatus::query()
                ->with(['application', 'application.construction'])
                ->where('user_id', $request->user()->id)
                ->where('status', 'unread')
                ->get();

            $badges = [];

            foreach ($badgesDb as $badge) {
                if (isset($badges[$badge->application->construction_id])) {
                    $badges[$badge->application->construction_id] += 1;
                    continue;
                } 

                $badges[$badge->application->construction_id] = 1;
            }

            return ['total' => $badgesDb->count(), 'badges' => $badges];
        }

        if ($request->type == 'contracts') {
            $badgesDb = ContractOpenedStatus::query()
                ->with(['contract'])
                ->where('user_id', $request->user()->id)
                ->where('status', 'unread')
                ->get();

            return ['total' => $badgesDb->count()];
        }

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
