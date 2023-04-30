<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required',
            'email' => 'required',
            'phone_number' => 'required',
            'password' => 'required',
            'username' => 'required',
            'registered_from' => '',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Error', $validator->errors());
        }

        DB::beginTransaction();
        try {
            $exists = Member::where('username', $request->username)->exists();
            if ($exists) {
                return $this->sendBadRequest('User Exists', 'User with this username is exists');
            }
            $input = $request->all();
            $input['password'] = Hash::make($request->password);
            $member = Member::create($input);
            $token = $member->createToken('member_token')->plainTextToken;
            DB::table('members')->where('id', $member->id)->update(['is_loggedin' => true, 'last_login' => Date::now()]);
            $data = DB::table('members')->where('id', $member->id)->first();
            DB::commit();

            return $this->sendResponse([
                'token'  => $token,
                'member' => $data,
            ], 'Registered Successfully');
        } catch (\Exception $err) {
            DB::rollBack();
            return $this->sendError(array('success' => 1), $err->getMessage());
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }
        Log::info($request->all());
        $member = Member::where('username', $request->username)->first();
        if (!$member) {
            return $this->sendError('Invalid Username/Password', null, 401);
        }

        if (!Hash::check($request->password, $member->password)) {
            return $this->sendError('Invalid Username/Password', null, 401);
        }

        Member::where('username', $request->username)->first()->update([
            'is_loggedin' => 1
        ]);
        return $this->sendResponse([
            'token' => $member->createToken('member_token')->plainTextToken,
            'member' => $member,
        ], 'Login Successfully');
    }

    public function logout(Request $request)
    {
        $user = Member::find(Auth::guard('member')->user()->id);
        $user->update(['is_loggedin' => 0]);
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse(null, 'Successfully Logout');
    }
    
    public function profile()
    {
        $member = Member::find(Auth::user()->id);
        return $this->sendResponse($member, 'Fetched');
    }
}
