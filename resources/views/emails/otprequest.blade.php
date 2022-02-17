@component('mail::message')

You have requested a password change, use the OTP code below

# {{ $code }}


Thanks,<br>
{{ config('app.name') }}
@endcomponent
