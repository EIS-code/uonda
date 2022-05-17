<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Constant;
use App\AppText;
use App\EmailTemplate;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $constants = Constant::all();
        return view('pages.settings.index', compact('constants'));
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
        return false;

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
        return false;
        /* Constant::where('id', decrypt($id))->delete();
		$request->session()->flash('success','Settings deleted successfully');
		return redirect(url()->previous()); */
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

    public function getEmailTemplates(Request $request)
    {
        $emailTemplates = EmailTemplate::all();

        return view('pages.settings.email-templates.index', compact('emailTemplates'));
    }

    public function editEmailTemplate($id, Request $request)
    {
        $id = decrypt($id);

        if (!empty($id)) {
            $emailTemplate = EmailTemplate::find($id);

            if (!empty($emailTemplate)) {
                $fields = !empty(EMAIL_DYNAMIC_FIELDS) ? json_decode(EMAIL_DYNAMIC_FIELDS, true) : [];

                return view('pages.settings.email-templates.edit', compact('emailTemplate', 'fields'));
            }
        }

        $request->session()->flash('alert-danger', 'Email template does not found.');

        return redirect()->route('settings.email.templates.get');
    }

    public function updateEmailTemplate(Request $request, $id)
    {
        $modal        = new EmailTemplate();
        $emailSubject = $request->get('email_subject', NULL);
        $emailBody    = $request->get('email_body', NULL);
        $data         = $request->all();

        $validator = $modal->validator($data);
        if ($validator->fails()) {
            echo json_encode(['success' => false, 'message' => $validator->errors()->first()]);

            exit;
        }

        // Find template
        $emailTemplate = $modal->find(decrypt($id));

        if (empty($emailTemplate)) {
            echo json_encode(['success' => false, 'message' => __('Email template does not found. Please reload page and try again.')]);

            exit;
        }

        $emailTemplate->email_subject = $emailSubject;

        $emailTemplate->email_body    = $emailBody;

        if ($emailTemplate->save()) {
            $request->session()->flash('alert-success', __('Email template updated successfully.'));

            echo json_encode(['success' => true, 'message' => __('Email template updated successfully.')]);

            exit;
        }

        echo json_encode(['success' => false, 'message' => __('Something went wrong. Please reload page and try again.')]);

        exit;
    }
}
