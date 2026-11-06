<x-app-layout>
    <div class="py-12 bg-[#FCFCFC] min-h-screen">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Page Header -->
            <div class="text-center mb-12">
                <a href="{{ route('dashboard') }}" class="text-[10px] tracking-[0.3em] uppercase font-bold text-gray-400 hover:text-black transition inline-block mb-4">
                    ← Back to Dashboard
                </a>
                <h1 class="font-serif text-4xl italic text-black mb-2">Account Settings</h1>
                <p class="text-xs text-gray-500 tracking-wide font-light">Manage your profile and preferences</p>
            </div>

            <!-- Profile Information Form -->
            <div class="bg-white border border-gray-100 p-8 md:p-10 mb-8 shadow-sm">
                <div class="mb-6 pb-6 border-b border-gray-100">
                    <h2 class="text-lg font-serif italic text-black tracking-widest">Profile Information</h2>
                    <p class="text-xs text-gray-500 mt-1">Update your account information and profile details.</p>
                </div>
                @include('profile.partials.update-profile-information-form')
            </div>

            <!-- Update Password Form -->
            <div class="bg-white border border-gray-100 p-8 md:p-10 mb-8 shadow-sm">
                <div class="mb-6 pb-6 border-b border-gray-100">
                    <h2 class="text-lg font-serif italic text-black tracking-widest">Change Password</h2>
                    <p class="text-xs text-gray-500 mt-1">Ensure your account is using a long, random password to stay secure.</p>
                </div>
                @include('profile.partials.update-password-form')
            </div>

            <!-- Delete Account Form -->
            <div class="bg-white border border-gray-100 p-8 md:p-10 shadow-sm border-red-100 bg-red-50/20">
                <div class="mb-6 pb-6 border-b border-red-100">
                    <h2 class="text-lg font-serif italic text-red-600 tracking-widest">Danger Zone</h2>
                    <p class="text-xs text-red-600/70 mt-1">Permanently delete your account and all associated data.</p>
                </div>
                @include('profile.partials.delete-user-form')
            </div>

        </div>
    </div>
</x-app-layout>
