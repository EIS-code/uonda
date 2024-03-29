<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Feed;
use Illuminate\Http\UploadedFile;
use Storage;
use App\Jobs\CreateFeedNotification;

class FeedsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $feeds = Feed::with('likedByUser')->orderBy('id', 'DESC')->get();
        return view('pages.feeds.index', compact('feeds'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.feeds.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $feed = new Feed();
        $data  = $request->all();

        $validator = $feed->validator($data);
        if ($validator->fails()) {
            $status = 500;
            $success_res  = [
                'code' => $status,
                'msg' => $validator->errors()->first()
            ];
            return response()->json($success_res, 500);
        }
        
        $fillableFields = $feed->getFillable();

        foreach ($data as $field => $value) {
            if (in_array($field, $fillableFields)) {
                $feed->{$field} = $value;
            }
        }

        $save = $feed->save();

        if ($save && array_key_exists('attachment', $data) && $data['attachment'] instanceof UploadedFile) {
            $id = $feed->id;

            $attachment = $data['attachment'];
            $pathInfos = pathinfo($attachment->getClientOriginalName());

            if (!empty($pathInfos['extension'])) {
                $folder = $feed->storageFolderName . '/' . $id;

                if (!empty($folder)) {
                    $fileName  = (empty($pathInfos['filename']) ? time() : str_replace(" ","-",$pathInfos['filename'])) . '_' . time() . '.' . $pathInfos['extension'];
                    $fileName  = removeSpaces($fileName);
                    $storeFile = $attachment->storeAs($folder, $fileName, $feed->fileSystem);

                    if ($storeFile) {
                        $feed = $feed->find($id);

                        $feed->attachment = $fileName;

                        $feed->save();
                    }
                }
            }
        }

        CreateFeedNotification::dispatch($feed->id)->delay(now()->addSeconds(2));

        $request->session()->flash('alert-success', 'Feed successfully created');

        echo $save;

        // return redirect()->route('feeds.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $feed = Feed::find(decrypt($id));
        return view('pages.feeds.show', compact('feed'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $feed = Feed::find(decrypt($id));
        return view('pages.feeds.edit', compact('feed'));
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
        $feed = Feed::find(decrypt($id));
        $prevAttachment = $feed->attachment;
        $data  = $request->all();

        $validator = $feed->validator($data, false, true);
        if ($validator->fails()) {
            $status = 500;
            $success_res  = [
                'code' => $status,
                'msg' => $validator->errors()->first()
            ];
            return response()->json($success_res, 500);
        }
        
        $fillableFields = $feed->getFillable();

        foreach ($data as $field => $value) {
            if (in_array($field, $fillableFields)) {
                $feed->{$field} = $value;
            }
        }

        if (array_key_exists('attachment', $data) && $data['attachment'] instanceof UploadedFile) {
            $attachment = $data['attachment'];
            if(!empty($prevAttachment)) {
                $prevAttachment = explode('/', $prevAttachment)[6];
                Storage::delete($feed->fileSystem . '/'. $feed->storageFolderName .'/' . decrypt($id) . '/' . $prevAttachment);
            }
            $pathInfos = pathinfo($attachment->getClientOriginalName());

            if (!empty($pathInfos['extension'])) {
                $folder = $feed->storageFolderName . '/' . decrypt($id);

                if (!empty($folder)) {
                    $fileName  = (empty($pathInfos['filename']) ? time() : str_replace(" ","-",$pathInfos['filename'])) . '_' . time() . '.' . $pathInfos['extension'];
                    $fileName  = removeSpaces($fileName);
                    $storeFile = $attachment->storeAs($folder, $fileName, $feed->fileSystem);

                    if ($storeFile) {
                        $feed->attachment = $fileName;

                    }
                }
            }
        }

        $isSave = $feed->save();

        $request->session()->flash('alert-success', 'Feed successfully updated');

        echo $isSave;

        // return redirect()->route('feeds.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        Feed::where('id', decrypt($id))->delete();
		$request->session()->flash('alert-success', 'Feed successfully removed');
		return redirect(url()->previous());
    }
}
