<section>
    <h2 class="text-lg font-medium">Employee Profile</h2>
    @include('profile.fields', ['completion' => false])
    @if(config('services.google.client_id') && !$user->google_id)
        <a class="mt-4 inline-block underline" href="{{ route('google.link') }}">Link your Google account</a>
    @endif
</section>
