<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\SubscriptionPlan;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plans = SubscriptionPlan::get();
        return view('pages.subscription-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.subscription-plans.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $plan = new SubscriptionPlan();
        $data  = $request->all();

        $validator = $plan->validator($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $fillableFields = $plan->getFillable();

        foreach ($data as $field => $value) {
            if (in_array($field, $fillableFields)) {
                $plan->{$field} = $value;
            }
        }
        $plan->save();
        $request->session()->flash('alert-success', 'Subscription Plan successfully created');
        return redirect()->route('subscription_plan.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $plan = SubscriptionPlan::find(decrypt($id));
        return view('pages.subscription-plans.show', compact('plan'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $plan = SubscriptionPlan::find(decrypt($id));
        return view('pages.subscription-plans.edit', compact('plan'));
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
        $plan = SubscriptionPlan::find(decrypt($id));
        $data  = $request->all();

        $validator = $plan->validator($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $fillableFields = $plan->getFillable();

        foreach ($data as $field => $value) {
            if (in_array($field, $fillableFields)) {
                $plan->{$field} = $value;
            }
        }

        $plan->save();
        $request->session()->flash('alert-success', 'Subscription Plan successfully updated');
        return redirect()->route('subscription_plan.index');
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
