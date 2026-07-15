<x-mail::message>
# Hello,

{{ $inviterName }} invited you to collaborate on the project **{{ $projectName }}**.

<x-mail::button :url="$url">
Accept Invitation
</x-mail::button>

This invitation will expire soon, so please accept it as soon as possible.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
