<x-mail::message>
# Hello,

You have been added to the project **{{ $projectName }}** as a {{ $role }}.

<x-mail::button :url="$url">
View Project
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
