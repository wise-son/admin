<?php

namespace App\Providers;

use App\Models\EmailSMSTemplate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;

class AuthServiceProvider extends ServiceProvider {
	/**
	 * The policy mappings for the application.
	 *
	 * @var array
	 */
	protected $policies = [
		// 'App\Model' => 'App\Policies\ModelPolicy',
	];

	/**
	 * Register any authentication / authorization services.
	 *
	 * @return void
	 */
	public function boot() {
		$this->registerPolicies();

		//Email Verification
		VerifyEmail::toMailUsing(function ($notifiable, $url) {
			return (new MailMessage)
				->subject('Verify Email Address')
				->line('Click the button below to verify your email address.')
				->action('Verify Email Address', $url);
		});

		//Forget Password
		ResetPassword::toMailUsing(function ($notifiable, $token) {
			$template = EmailSMSTemplate::where('slug', 'RESET_PASSWORD')->first();

			if ($template && $template->email_status == 1) {

				$replace['password_reset_link'] = url(config('app.url') . route('password.reset', $token, false));
				$message = processShortCode($template->email_body, $replace);

				return (new MailMessage)
					->subject($template->subject)
					->markdown('email.forget_password', ['message' => $message]);

			} else {
				return (new MailMessage)
					->subject('Reset Password Notification')
					->line('You are receiving this email because we received a password reset request for your account.')
					->action('Reset Password', url(config('app.url') . route('password.reset', $token, false)))
					->line('This password reset link will expire in 60 minutes.')
					->line('If you did not request a password reset, no further action is required.');
			}

		});

	}
}
