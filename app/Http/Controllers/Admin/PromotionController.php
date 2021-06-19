<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Promotions;
use Illuminate\Http\UploadedFile;
use Storage;
use Carbon\Carbon;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $promotions = Promotions::get();
        return view('pages.promotions.index', compact('promotions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.promotions.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $promotion = new Promotions();
        $data  = $request->all();

        $requiredFileds = [
            'photo'   => ['required']
        ];
        
        $validator = $promotion->validator($data, $requiredFileds);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $fillableFields = $promotion->getFillable();

        foreach ($data as $field => $value) {
            if (in_array($field, $fillableFields)) {
                $promotion->{$field} = $value;
            }
        }
        $promotion->expiry_date = Carbon::parse($request->expiry_date);

        $save = $promotion->save();

        if ($save && array_key_exists('photo', $data) && $data['photo'] instanceof UploadedFile) {
            $id = $promotion->id;

            $attachment = $data['photo'];
            $pathInfos = pathinfo($attachment->getClientOriginalName());

            if (!empty($pathInfos['extension'])) {
                $folder = $promotion->storageFolderName . '/' . $id;

                if (!empty($folder)) {
                    $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                    $fileName  = removeSpaces($fileName);
                    $storeFile = $attachment->storeAs($folder, $fileName, $promotion->fileSystem);

                    if ($storeFile) {
                        $promotion = $promotion->find($id);

                        $promotion->photo = $fileName;

                        $promotion->save();
                    }
                }
            }
        }
        $request->session()->flash('alert-success', 'Promotion successfully created');
        return redirect()->route('promotions.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $promotion = Promotions::find(decrypt($id));
        return view('pages.promotions.show', compact('promotion'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $promotion = Promotions::find(decrypt($id));
        return view('pages.promotions.edit', compact('promotion'));
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
        $promotion = Promotions::find(decrypt($id));
        $prevAttachment = $promotion->photo;
        $data  = $request->all();

        $requiredFileds = [
            'voucher_code'   => ['unique:promotions,voucher_code,'.decrypt($id)]
        ];
        
        $validator = $promotion->validator($data, $requiredFileds);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $fillableFields = $promotion->getFillable();

        foreach ($data as $field => $value) {
            if (in_array($field, $fillableFields)) {
                if($field == 'expiry_date') {
                    $promotion->{$field} = Carbon::parse($request->expiry_date);
                } else {
                    $promotion->{$field} = $value;
                }
            }
        }

        if (array_key_exists('photo', $data) && $data['photo'] instanceof UploadedFile) {
            $attachment = $data['photo'];
            if(!empty($prevAttachment)) {
                $array = explode('/', $prevAttachment);
                $key = array_key_last($array);
                $image = $array[$key];
                Storage::delete($promotion->fileSystem . '/'. $promotion->storageFolderName .'/' .decrypt($id) .'/'. $image);
            }
            $pathInfos = pathinfo($attachment->getClientOriginalName());

            if (!empty($pathInfos['extension'])) {
                $folder = $promotion->storageFolderName . '/' . decrypt($id);

                if (!empty($folder)) {
                    $fileName  = (empty($pathInfos['filename']) ? time() : $pathInfos['filename']) . '_' . time() . '.' . $pathInfos['extension'];
                    $fileName  = removeSpaces($fileName);
                    $storeFile = $attachment->storeAs($folder, $fileName, $promotion->fileSystem);

                    if ($storeFile) {
                        $promotion->photo = $fileName;
                    }
                }
            }
        }
        $promotion->save();
        $request->session()->flash('alert-success', 'Promotion successfully updated');
        return redirect()->route('promotions.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        Promotions::where('id', decrypt($id))->delete();
		$request->session()->flash('alert-success', 'Promotion successfully removed');
		return redirect(url()->previous());
    }
}
