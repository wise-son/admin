<table class="table table-bordered">
	<tr>
		<td colspan="2" class="text-center"><img class="thumb-image-sm img-thumbnail"
				src="{{ profile_picture($user->profile_picture) }}"></td>
	</tr>
	<tr><td>{{ _lang('Name') }}</td><td>{{ $user->name }}</td></tr>
	<tr><td>{{ _lang('Email') }}</td><td>{{ $user->email }}</td></tr>
	<tr><td>{{ _lang('User Type') }}</td><td>{{ $user->user_type }}</td></tr>
	<tr><td>{{ _lang('User Role') }}</td><td>{{ $user->role->name }}</td></tr>
	<tr><td>{{ _lang('Status') }}</td><td>{!! xss_clean(user_status($user->status)) !!}</td></tr>
	<tr><td>{{ _lang('Created At') }}</td><td>{{ $user->created_at }}</td></tr>
</table>

