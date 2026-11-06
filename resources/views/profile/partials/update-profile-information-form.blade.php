<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" class="space-y-6">
    @csrf
    @method('patch')

    <div>
        <label for="name" class="block text-[10px] uppercase tracking-[0.2em] font-bold text-gray-500 mb-2">Full Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus
            class="w-full border-b border-gray-200 px-0 py-2 text-sm text-black focus:ring-0 focus:border-black bg-transparent">
        @if ($errors->has('name'))<p class="text-xs text-red-500 mt-1">{{ $errors->first('name') }}</p>@endif
    </div>

    <div>
        <label for="email" class="block text-[10px] uppercase tracking-[0.2em] font-bold text-gray-500 mb-2">Email Address</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
            class="w-full border-b border-gray-200 px-0 py-2 text-sm text-black focus:ring-0 focus:border-black bg-transparent">
        @if ($errors->has('email'))<p class="text-xs text-red-500 mt-1">{{ $errors->first('email') }}</p>@endif

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <p class="text-xs text-gray-500 mt-2">
                Your email is unverified.
                <button form="send-verification" class="underline text-black font-semibold hover:opacity-60">Resend verification.</button>
            </p>
            @if (session('status') === 'verification-link-sent')
                <p class="text-xs text-green-600 mt-1">Verification link sent.</p>
            @endif
        @endif
    </div>

    <div>
        <label for="birth_date" class="block text-[10px] uppercase tracking-[0.2em] font-bold text-gray-500 mb-2">Birthday</label>
        <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}"
            class="w-full border-b border-gray-200 px-0 py-2 text-sm text-black focus:ring-0 focus:border-black bg-transparent">
        @if ($errors->has('birth_date'))<p class="text-xs text-red-500 mt-1">{{ $errors->first('birth_date') }}</p>@endif
        <p class="text-xs text-gray-400 mt-1">Birthday reward will be credited automatically on your birthday.</p>
    </div>

    <div class="flex items-center gap-4 pt-2">
        <button type="submit" class="bg-black text-white text-[10px] py-3 px-8 uppercase tracking-[0.3em] font-bold hover:opacity-70 transition">
            Save Changes
        </button>
        @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-xs text-green-600">Saved.</p>
        @endif
    </div>
</form>
