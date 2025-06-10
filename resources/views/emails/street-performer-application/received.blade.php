@component('mail::message')
# 🎭 Street Performer Submission

Hello,

A new street performer has submitted their details. Here is the information:

- **Name:** {{ $streetPerformerData['name'] }}
- **Stage Name:** {{ $streetPerformerData['stageName'] }}
- **Country:** {{ $streetPerformerData['country'] }}
- **Discipline:** {{ $streetPerformerData['discipline'] }}

@component('mail::button', ['url' => $streetPerformerData['videoLink']])
🎥 Watch Performance Video
@endcomponent

Thanks,
{{ config('app.name') }}
@endcomponent