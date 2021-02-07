<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\UserReportQuestion;
use App\UserReport;

class UserReportsQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $questions = UserReportQuestion::get();
        return view('pages.reports-questions.index', compact('questions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $question = new UserReportQuestion();
        $options = $question->questionTypes;
        return view('pages.reports-questions.add', compact('options'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $question = new UserReportQuestion();
        $data  = $request->all();

        $validator = $question->validator($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $fillableFields = $question->getFillable();
        foreach ($data as $field => $value) {
            if (in_array($field, $fillableFields)) {
                $question->{$field} = $value;
            }
        }

        $save = $question->save();

        $request->session()->flash('alert-success', 'Question successfully created');
        return redirect()->route('reports-questions.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $question = UserReportQuestion::find(decrypt($id));
        return view('pages.reports-questions.show', compact('question'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $question = UserReportQuestion::find(decrypt($id));
        $options = $question->questionTypes;
        return view('pages.reports-questions.edit', compact('options', 'question'));
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
        $question = UserReportQuestion::find(decrypt($id));
        $data  = $request->all();

        $validator = $question->validator($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        $fillableFields = $question->getFillable();
        foreach ($data as $field => $value) {
            if (in_array($field, $fillableFields)) {
                $question->{$field} = $value;
            }
        }

        $save = $question->save();

        $request->session()->flash('alert-success', 'Question successfully updated');
        return redirect()->route('reports-questions.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        UserReportQuestion::where('id', decrypt($id))->delete();
		$request->session()->flash('alert-success', 'Question successfully removed');
		return redirect(url()->previous());
    }

    //Function to get the user-reports 
    public function showUserReports(Request $request) {
        $reports = UserReport::with('ReportQuestions', 'User')->get();
        return view('pages.reports-questions.user-reports-index', compact('reports'));
    }
}
