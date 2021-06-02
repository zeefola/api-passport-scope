@component('mail::message')
    # Successful Registration

    Welcome onboard, You've succcessfully register for my page.

    {{-- @component('mail::button', ['url' => ''])
        Button Text
    @endcomponent --}}

    Thanks,
    {{ config('app.name') }}
@endcomponent
