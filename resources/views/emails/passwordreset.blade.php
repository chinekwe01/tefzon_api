@component('mail::message')

You just requested a password change, use the link to reset your password

@component('mail::button', ['url' => $details['url']])
Reset Link
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent