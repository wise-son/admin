@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('SMS Template Details') }}</h4>
			</div>
			<div class="card-body">
				<table class="table table-striped">
					<tr><td>{{ $emailtemplate->subject }}</td></tr>
					<tr><td>{!! xss_clean($emailtemplate->sms_body) !!}</td></tr>
				</table>
			</div>
		</div>
	</div>
</div>
@endsection


