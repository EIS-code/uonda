<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\PromoCode;

class PromoCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $promo_codes = PromoCode::get();
        return view('pages.promo-codes.index', compact('promo_codes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.promo-codes.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $code = new PromoCode();
        $data  = $request->all();
        $validator = $code->validator($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $fillableFields = $code->getFillable();
        
        $code->promo_code = $request->promo_code;
        $code->amount = !empty($request->amount) ? $request->amount : '';
        $code->percentage = !empty($request->percentage) ? $request->percentage : '';
        $code->status = array_key_exists('status', $data) ? 1 : 0 ;
        $code->save();
        $request->session()->flash('alert-success', 'Promo code successfully created');
        return redirect()->route('promo-codes.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $code = PromoCode::find(decrypt($id));
        return view('pages.promo-codes.show', compact('code'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $code = PromoCode::find(decrypt($id));
        return view('pages.promo-codes.edit', compact('code'));
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
        $code = PromoCode::find(decrypt($id));
        $data  = $request->all();

        $validator = $code->validator($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $fillableFields = $code->getFillable();
        $code->promo_code = $request->promo_code;
        $code->amount = !empty($request->amount) ? $request->amount : '';
        $code->percentage = !empty($request->percentage) ? $request->percentage : '';
        $code->status = array_key_exists('status', $data) ? 1 : 0;
        $code->save();
        $request->session()->flash('alert-success', 'Promo code successfully updated');
        return redirect()->route('promo-codes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
