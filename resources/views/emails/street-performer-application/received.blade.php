<x-mail::message>
# Introduction

A new user applied as a street performer.

Details:
Name: {{ $streetPerformerData['name'] }}
Stage Name: {{ $streetPerformerData['stageName'] }}
Country: {{ $streetPerformerData['country'] }}
Discipline: {{ $streetPerformerData['discipline'] }}
Video Link: {{ $streetPerformerData['videoLink'] }}

<x-mail::button :url="''">
View User
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
