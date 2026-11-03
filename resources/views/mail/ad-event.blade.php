<x-mail::alertNotification>
⚠️ Action Required: An ad is waiting for your review.
</x-mail::alertNotification>
<x-mail::message>

Hello {{ $name }},
---

# {{ $heading }}

<x-mail::panel>
**{{ $ad->title }}**<br>
Posted by: {{ $ad->user->name }}
</x-mail::panel>
---
<x-mail::button :url="$url">
{{ $buttonText }}
</x-mail::button>

</x-mail::message>
