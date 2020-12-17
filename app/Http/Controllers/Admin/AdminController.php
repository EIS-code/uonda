<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;
use Hash;
use Illuminate\Http\UploadedFile;
use Storage;

class AdminController extends Controller
{
    public function editProfile(Request $request) {
        $user = Auth::user();
        return view('pages.admin-profile.edit', compact('user'));
    }

    public function updateProfile(Request $request) {
        $user = Auth::user();
        $data = $request->all();
        $prevAttachment = $user->profile_pic;
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => ['nullable', 'string', 'min:8'],
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
        if($request->has('password')) {
            $user->password = Hash::make($request->password);
        }
        if (array_key_exists('attachment', $data) && ($data['attachment'] instanceof UploadedFile)) {
            $attachment = $data['attachment'];
            if(!empty($prevAttachment)) {
                Storage::delete('/public/admin-profile//' . $prevAttachment);
            }
            $pathInfos = pathinfo($attachment->getClientOriginalName());
            if (!empty($pathInfos['extension'])) {

                $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                $storeFile = $attachment->storeAs('admin-profile', $fileName, 'public');

                if ($storeFile) {
                    $user->profile_pic = $fileName;

                }
            }
        }
        $user->save();
        $request->session()->flash('alert-success', 'Profile successfully updated');
        return redirect()->back();
    }
}
