<?php

namespace App\Http\Controllers;

use App\Models\EmailSMSTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SMSTemplateController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $emailtemplates = EmailSMSTemplate::where('template_mode', 0)
                                          ->orWhere('template_mode', 2)
                                          ->get();
        return view('backend.administration.sms_template.list', compact('emailtemplates'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        $emailtemplate = EmailSMSTemplate::find($id);

        if (!$request->ajax()) {
            return view('backend.administration.sms_template.view', compact('emailtemplate', 'id'));
        } else {
            return view('backend.administration.sms_template.modal.view', compact('emailtemplate', 'id'));
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $emailtemplate = EmailSMSTemplate::find($id);

        if (!$request->ajax()) {
            return view('backend.administration.sms_template.edit', compact('emailtemplate', 'id'));
        } else {
            return view('backend.administration.sms_template.modal.edit', compact('emailtemplate', 'id'));
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'name'       => 'required',
            'sms_body'   => 'required',
            'sms_status' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('sms_templates.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $emailtemplate             = EmailSMSTemplate::find($id);
        $emailtemplate->name       = $request->input('name');
        $emailtemplate->sms_body   = $request->input('sms_body');
        $emailtemplate->sms_status = $request->input('sms_status');

        $emailtemplate->save();

        if (!$request->ajax()) {
            return redirect()->route('sms_templates.index')->with('success', _lang('Updated successfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated successfully'), 'data' => $emailtemplate]);
        }
    }
}
