@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header d-flex align-items-center">
				<h4 class="header-title">{{ _lang('Create Email Template') }}</h4>
			</div>
			<div class="card-body">
			  <form method="post" class="validate" autocomplete="off" action="{{ route('email_templates.store') }}" enctype="multipart/form-data">
					@csrf
					<div class="col-md-12">
						<div class="form-group">
							<label class="control-label">{{ _lang('Name') }}</label>
							<input type="text" class="form-control" name="name" value="{{ old('name') }}">
						</div>
					</div>

					<div class="col-md-12">
						<div class="form-group">
							<label class="control-label">{{ _lang('Slug') }}</label>
							<input type="text" class="form-control" name="slug" value="{{ old('slug') }}">
						</div>
					</div>

					<div class="col-md-12">
						<div class="form-group">
							<label class="control-label">{{ _lang('Subject') }}</label>
							<input type="text" class="form-control" name="subject" value="{{ old('subject') }}" required>
						</div>
					</div>

					<div class="col-md-12">
						<div class="form-group">
							<label class="control-label">{{ _lang('Body') }}</label>
							<textarea class="form-control summernote" name="email_body">{{ old('body') }}</textarea>
						</div>
					</div>

					<div class="col-md-12">
						<div class="form-group">
							<label class="control-label">{{ _lang('Short Code') }}</label>
							<textarea class="form-control" name="shortcode" required>{{ old('shortcode') }}</textarea>
						</div>
					</div>

					<div class="col-md-12">
						<div class="form-group">
							<label class="control-label">{{ _lang('Status') }}</label>
							<select class="form-control auto-select" name="email_status" data-selected="{{ old('email_status',1) }}" required>
								<option value="1">{{ _lang('Active') }}</option>
								<option value="0">{{ _lang('Deactivate') }}</option>
							</select>
						</div>
					</div>

					<div class="form-group">
						<div class="col-md-12">
							<button type="submit" class="btn btn-primary"><i class="ti-check-box"></i>&nbsp;{{ _lang('Save') }}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection


