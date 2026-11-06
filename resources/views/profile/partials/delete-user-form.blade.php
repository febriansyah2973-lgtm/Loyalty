<div x-data="{ showModal: false }">
    <p class="text-xs text-gray-500 mb-6">Once your account is deleted, all data will be permanently removed. This action cannot be undone.</p>

    <button type="button" x-on:click="showModal = true"
        class="bg-red-600 text-white text-[10px] py-3 px-8 uppercase tracking-[0.3em] font-bold hover:bg-red-700 transition">
        Delete Account
    </button>

    <!-- Confirm Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center" style="display:none">
        <div class="absolute inset-0 bg-black/60" x-on:click="showModal = false"></div>
        <div class="relative bg-white border border-gray-200 p-8 max-w-md w-full mx-4 shadow-2xl">
            <h3 class="font-serif text-xl italic text-black mb-2">Delete Account?</h3>
            <p class="text-xs text-gray-500 mb-6">Enter your password to confirm permanent deletion of your account.</p>

            <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4">
                @csrf
                @method('delete')

                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-gray-500 mb-2">Password</label>
                    <input name="password" type="password" placeholder="••••••••"
                        class="w-full border-b border-gray-200 px-0 py-2 text-sm text-black focus:ring-0 focus:border-black bg-transparent">
                    @if ($errors->userDeletion->has('password'))<p class="text-xs text-red-500 mt-1">{{ $errors->userDeletion->first('password') }}</p>@endif
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" x-on:click="showModal = false"
                        class="text-[10px] uppercase tracking-widest text-gray-500 hover:text-black px-6 py-2 border border-gray-200 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="bg-red-600 text-white text-[10px] py-2 px-6 uppercase tracking-[0.2em] font-bold hover:bg-red-700 transition">
                        Confirm Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
