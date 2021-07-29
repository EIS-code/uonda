<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;
use Hash;
use Illuminate\Http\UploadedFile;
use Storage;
use DB;
use App\User;

class AdminController extends Controller
{
    public function editProfile(Request $request) {
        $user = User::select("*" , "profile as new_profile")->where("id",Auth::user()->id)->first();
        // echo "<pre>";
        // print_r($user);exit;
        return view('pages.admin-profile.edit', compact('user'));
    }

    public function updateProfile(Request $request) {
        $user = Auth::user();
        $data = $request->all();
        // $prevAttachment = $user->profile;
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => ['nullable', 'required_with:confirm-password', 'confirmed', 'string'],
            'job_position' => 'nullable',
            'attachment' => 'sometimes|mimes:jpg,jpeg,png'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
             return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->job_position = $request->has('job_position') ? $request->job_position : NULL;
        if($request->has('password') && !empty($request->get('password'))) {
            $user->password = Hash::make($request->password);
        }
        if (array_key_exists('attachment', $data) && ($data['attachment'] instanceof UploadedFile)) {
            
            $attachment = $data['attachment'];
            // echo "<pre>";
            // print_r($user);exit;
            // if(!empty($prevAttachment)) {
            //     echo "ININ";exit;
            //     Storage::delete('/public/admin-profile//' . $prevAttachment);
            // }
            // echo "ININ1";exit;
            $pathInfos = pathinfo($attachment->getClientOriginalName());
            if (!empty($pathInfos['extension'])) {

                $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                $fileName  = removeSpaces($fileName);
                $storeFile = $attachment->storeAs('admin-profile', $fileName, 'public');
                $user->profile = $fileName;
                DB::table('users')->where("id", Auth::user()->id)->update(['profile' => $user->profile]);
                if ($storeFile) {
                    $user->profile = $fileName;
                    DB::table('users')->where("id", Auth::user()->id)->update(['profile' => $user->profile]);
                }
            }
        }
        
        $user->save();
        $request->session()->flash('alert-success', 'Profile successfully updated');
        return redirect()->back();
    }
}
