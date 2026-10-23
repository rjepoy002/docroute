<x-guest-layout>
    <dialog open data-profile-dialog aria-labelledby="completion-title" aria-describedby="completion-description" class="profile-dialog">
        <header class="auth-heading">
            <span class="auth-kicker">ONE LAST STEP</span>
            <h2 id="completion-title">Complete Your Profile</h2>
            <p id="completion-description">Add your employee details to get started with DocRoute.</p>
        </header>
        @include('profile.fields', ['completion' => true])
        <form method="post" action="{{ route('logout') }}">@csrf<button class="auth-text-button" type="submit">Sign out</button></form>
    </dialog>
</x-guest-layout>
