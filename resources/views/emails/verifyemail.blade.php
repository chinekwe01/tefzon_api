@component('mail::message')
# Email verification


@component('mail::button', ['url' => $url])
Click to verify your email address
@endcomponent
<br>
or use the link below to verify your email address <br>
<a href="{{ $url }}">{{ $url }}</a>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
