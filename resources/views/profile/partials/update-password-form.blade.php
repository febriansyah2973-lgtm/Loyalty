<form method="post" action="{{ route('password.update') }}" class="space-y-6">
    @csrf
    @method('put')

    <div>
        <label for="update_password_current_password" class="block text-[10px] uppercase tracking-[0.2em] font-bold text-gray-500 mb-2">Current Password</label>
        <input id="update_password_current_password" name="current_password" type="password"
            class="w-full border-b border-gray-200 px-0 py-2 text-sm text-black focus:ring-0 focus:border-black bg-transparent">
        @if ($errors->updatePassword->has('current_password'))<p class="text-xs text-red-500 mt-1">{{ $errors->updatePassword->first('current_password') }}</p>@endif
    </div>

    <div>
        <label for="update_password_password" class="block text-[10px] uppercase tracking-[0.2em] font-bold text-gray-500 mb-2">New Password</label>
        <input id="update_password_password" name="password" type="password"
            class="w-full border-b border-gray-200 px-0 py-2 text-sm text-black focus:ring-0 focus:border-black bg-transparent">
        @if ($errors->updatePassword->has('password'))<p class="text-xs text-red-500 mt-1">{{ $errors->updatePassword->first('password') }}</p>@endif
    </div>

    <div>
        <label for="update_password_password_confirmation" class="block text-[10px] uppercase tracking-[0.2em] font-bold text-gray-500 mb-2">Confirm New Password</label>
        <input id="update_password_password_confirmation" name="password_confirmation" type="password"
            class="w-full border-b border-gray-200 px-0 py-2 text-sm text-black focus:ring-0 focus:border-black bg-transparent">
        @if ($errors->updatePassword->has('password_confirmation'))<p class="text-xs text-red-500 mt-1">{{ $errors->updatePassword->first('password_confirmation') }}</p>@endif
    </div>

    <div class="flex items-center gap-4 pt-2">
        <button type="submit" class="bg-black text-white text-[10px] py-3 px-8 uppercase tracking-[0.3em] font-bold hover:opacity-70 transition">
            Update Password
        </button>
        @if (session('status') === 'password-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-xs text-green-600">Saved.</p>
        @endif
    </div>
</form>
