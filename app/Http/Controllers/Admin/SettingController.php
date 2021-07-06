<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Constant;
use App\AppText;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        $constants = Constant::all();
//        return view('pages.settings.index', compact('constants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.settings.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $constant = new Constant();
        $data  = $request->all();
        // $data['is_removed'] = 0;
        // if(array_key_exists('status', $data)) {
        //     $data['is_removed'] = 1;
        // }
        $validator = $constant->validator($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $fillableFields = $constant->getFillable();

        foreach ($data as $field => $value) {
            if (in_array($field, $fillableFields)) {
                $constant->{$field} = $value;
            }
        }

        $constant->save();
        $request->session()->flash('alert-success', 'Setting successfully created');
        return redirect()->route('settings.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $constant = Constant::find(decrypt($id));
        return view('pages.settings.edit', compact('constant'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $constant = Constant::find(decrypt($id));
        $data  = $request->all();
        // $data['is_removed'] = 0;
        // if(array_key_exists('status', $data)) {
        //     $data['is_removed'] = 1;
        // }
        $validator = $constant->validator($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $fillableFields = $constant->getFillable();

        foreach ($data as $field => $value) {
            if (in_array($field, $fillableFields)) {
                $constant->{$field} = $value;
            }
        }

        $constant->save();
        $request->session()->flash('alert-success', 'Setting successfully updated');
        return redirect()->route('settings.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        Constant::where('id', decrypt($id))->delete();
		$request->session()->flash('success','Settings deleted successfully');
		return redirect(url()->previous());
    }
    
    public function getConstants() {
        
        $constants = Constant::all();
        return view('pages.settings.index', compact('constants'));
    }
    
    public function getNotificationText() {
        
        $notificationTexts = AppText::where('type', (string) AppText::NOTIFICATION)->get();
        return view('pages.settings.appTexts.notificationText', compact('notificationTexts'));
    }
    
    public function getApiResponseText() {
        
        $apiResponseTexts = AppText::where('type', (string) AppText::API_RESPONSE)->get();
        return view('pages.settings.appTexts.apiResponseText', compact('apiResponseTexts'));
    }
    
    public function updateNotificationText(Request $request) {
        
        if ($request->ajax()) {
            AppText::find($request->id)->update(['show_text' => $request->show_text]);
            return response()->json(['success' => true]);
        }
    }
    
    public function updateApiResponseText(Request $request) {
        
        if ($request->ajax()) {
            AppText::find($request->id)->update(['show_text' => $request->show_text]);
            return response()->json(['success' => true]);
        }
    }
}
