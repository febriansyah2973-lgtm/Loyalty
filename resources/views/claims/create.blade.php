<x-app-layout>
    <div class="py-16 min-h-screen" style="background:#FAF8F5;">
        <div class="max-w-xl mx-auto px-6 lg:px-8">
            
            <div class="mb-12 text-center">
                <a href="{{ route('dashboard') }}" style="font-size:10px;letter-spacing:0.3em;text-transform:uppercase;font-weight:600;color:#A8A29E;text-decoration:none;" class="hover:text-stone-800 transition">
                    ← Back to Dashboard
                </a>
                <p style="font-size:9px;letter-spacing:0.5em;text-transform:uppercase;color:#B8964E;font-weight:600;margin:20px 0 8px;">Loyalty Program</p>
                <h2 style="font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:300;color:#1C1916;font-style:italic;line-height:1.1;">Claim Purchase Points</h2>
                <div class="gold-divider" style="margin:14px auto;"></div>
                <p style="font-size:11px;color:#A8A29E;margin-top:8px;">Submit your boutique or online store receipt to earn exclusive points.</p>
                <div class="mt-4 p-3 bg-amber-50 border border-amber-200 text-amber-900 text-xs tracking-wide text-left">
                    <p class="font-bold mb-1">✦ Formula Perolehan Poin:</p>
                    <p>Formula Dasar: (Total Belanja / 100.000) &times; 10 Poin</p>
                    <p class="mt-1 text-[11px] text-amber-800">
                        Multiplier Tier: <strong>Reguler (1.0x)</strong> &bull; <strong>Silver (1.5x)</strong> &bull; <strong>Gold (2.0x)</strong>.
                        @auth
                            Status Anda saat ini: <strong class="underline">{{ Auth::user()->tier }} ({{ Auth::user()->getTierMultiplier() }}x)</strong>.
                        @endauth
                    </p>
                </div>
            </div>

            <form action="{{ route('claims.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8 bg-white border border-stone-100 p-10 shadow-sm">
                @csrf
                
                @if ($errors->any())
                <div style="background:#FEF3C7;border:1px solid #F59E0B;padding:12px 16px;margin-bottom:8px;">
                    <ul style="font-size:11px;color:#92400E;margin:0;padding-left:16px;">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <div>
                    <label style="display:block;font-size:9px;letter-spacing:0.3em;text-transform:uppercase;font-weight:700;color:#1C1916;margin-bottom:10px;">Order Number</label>
                    <input type="text" name="order_number" value="{{ old('order_number') }}" placeholder="e.g. #KHN12345" 
                           style="width:100%;border:none;border-bottom:1px solid #E7E5E4;padding:10px 0;font-size:14px;background:transparent;outline:none;" required>
                </div>

                <div>
                    <label style="display:block;font-size:9px;letter-spacing:0.3em;text-transform:uppercase;font-weight:700;color:#1C1916;margin-bottom:10px;">Total Purchase (Rp)</label>
                    <input type="number" name="amount" value="{{ old('amount') }}" placeholder="e.g. 250000" 
                           style="width:100%;border:none;border-bottom:1px solid #E7E5E4;padding:10px 0;font-size:14px;background:transparent;outline:none;" required>
                </div>

                <div>
                    <label style="display:block;font-size:9px;letter-spacing:0.3em;text-transform:uppercase;font-weight:700;color:#1C1916;margin-bottom:10px;">Upload Receipt / Proof</label>
                    <div style="border:1px dashed #D6D3D1;background:#FAF8F5;padding:32px;text-align:center;">
                        <svg style="width:32px;height:32px;margin:0 auto 12px;color:#A8A29E;" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <label style="cursor:pointer;font-size:11px;font-weight:700;letter-spacing:0.1em;color:#1C1916;">
                            <span>Select file</span>
                            <input type="file" name="proof_image" id="proof_image" class="sr-only" required onchange="updateFileName(this)">
                        </label>
                        <span style="font-size:11px;color:#A8A29E;"> or drag and drop</span>
                        <p id="file-name-preview" style="font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:#A8A29E;margin-top:8px;">PNG, JPG up to 2MB</p>
                    </div>
                </div>

                <button type="submit" style="width:100%;background:#1C1916;color:white;font-size:10px;padding:16px;letter-spacing:0.3em;text-transform:uppercase;font-weight:700;border:none;cursor:pointer;transition:background 0.2s;" onmouseover="this.style.background='#B8964E'" onmouseout="this.style.background='#1C1916'">
                    Submit Claim
                </button>
            </form>
        </div>
    </div>

    <script>
        function updateFileName(input) {
            const preview = document.getElementById('file-name-preview');
            if (input.files && input.files[0]) {
                preview.textContent = 'Selected: ' + input.files[0].name;
                preview.style.color = '#B8964E';
            } else {
                preview.textContent = 'PNG, JPG up to 2MB';
                preview.style.color = '#A8A29E';
            }
        }
    </script>
</x-app-layout>