<x-app-layout>
    <div class="py-12 min-h-screen" style="background:#FAF8F5;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
                <div>
                    <p style="font-size:9px;letter-spacing:0.5em;text-transform:uppercase;color:#B8964E;font-weight:600;margin-bottom:8px;">Loyalty Inbox</p>
                    <h1 style="font-family:'Cormorant Garamond',serif;font-size:2.5rem;font-weight:300;font-style:italic;color:#1C1916;">Notifications</h1>
                    <div class="gold-divider" style="margin-top:10px;"></div>
                </div>
                <a href="{{ route('dashboard') }}" style="font-size:10px;letter-spacing:0.25em;text-transform:uppercase;font-weight:600;color:#A8A29E;text-decoration:none;">← Dashboard</a>
            </div>

            <div style="background:white;border:1px solid #E7E5E4;">
                <div>
                    @forelse($notifications as $notification)
                        <div style="padding:24px 28px;border-bottom:1px solid #F5F5F4;background:{{ is_null($notification->read_at) ? '#FDFCFA' : 'white' }};">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;">
                                <div style="flex:1;">
                                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
                                        <span style="font-size:9px;letter-spacing:0.3em;text-transform:uppercase;font-weight:700;color:#B8964E;">{{ $notification->data['type'] ?? 'info' }}</span>
                                        @if(is_null($notification->read_at))
                                            <span style="width:6px;height:6px;border-radius:50%;background:#B8964E;display:inline-block;"></span>
                                        @endif
                                    </div>
                                    <h2 style="font-size:15px;font-weight:600;color:#1C1916;margin-bottom:4px;">{{ $notification->data['title'] ?? 'Update' }}</h2>
                                    <p style="font-size:13px;color:#78716C;line-height:1.6;">{{ $notification->data['message'] ?? '' }}</p>
                                    <p style="font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:#A8A29E;margin-top:10px;">{{ $notification->created_at->format('d M Y, H:i') }}</p>
                                </div>
                                <div>
                                    @if(is_null($notification->read_at))
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf
                                            <button style="font-size:9px;letter-spacing:0.2em;text-transform:uppercase;font-weight:700;border:1px solid #1C1916;padding:8px 16px;background:transparent;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='#1C1916';this.style.color='white';" onmouseout="this.style.background='transparent';this.style.color='#1C1916';">Mark Read</button>
                                        </form>
                                    @else
                                        <span style="font-size:9px;letter-spacing:0.2em;text-transform:uppercase;color:#A8A29E;">Read</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="padding:60px;text-align:center;">
                            <p style="font-family:'Cormorant Garamond',serif;font-style:italic;color:#A8A29E;font-size:16px;">No notifications yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-8">{{ $notifications instanceof \Illuminate\Pagination\AbstractPaginator ? $notifications->links() : '' }}</div>
        </div>
    </div>
</x-app-layout>