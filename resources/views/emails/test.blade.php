<x-mail::message>
# Hello {{ $username }},

This is a test email from PlanHive. If you received it, your SMTP settings are working correctly.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
