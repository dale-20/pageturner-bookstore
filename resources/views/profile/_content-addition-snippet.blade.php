{{--
    Add this inside _content.blade.php, in the col-md-8 section,
    after the closing </div> of the main profile edit card and
    after the <hr class="my-4"> line (around line 282).

    Place it BEFORE the Danger Zone block.

    ---

    Replace this section in _content.blade.php:

        <hr class="my-4">

        {{-- Danger Zone — customers only --}}
        @if(!$isAdmin)

    With:

        <hr class="my-4">

        {{-- Two-Factor Authentication --}}
        @include('profile._two-factor')

        <hr class="my-4">

        {{-- Danger Zone — customers only --}}
        @if(!$isAdmin)

        <hr class="my-4">

        {{-- Two-Factor Authentication --}}
        @include('profile._two-factor')

        <hr class="my-4">

        {{-- Danger Zone — customers only --}}
        @if(!$isAdmin)