<x-app-layout>
    <div class="py-12 min-h-screen" style="background:#FAF8F5;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <p style="font-size:9px;letter-spacing:0.5em;text-transform:uppercase;color:#B8964E;font-weight:600;margin-bottom:8px;">Operational Tool</p>
                <h1 style="font-family:'Cormorant Garamond',serif;font-size:2.5rem;font-weight:300;font-style:italic;color:#1C1916;">Scan QR Member & Kasir Kasir</h1>
                <div class="gold-divider" style="margin:12px auto;"></div>
                <p style="font-size:12px;color:#A8A29E;margin-top:8px;">Pindai QR member untuk verifikasi data, cek poin, dan gunakan voucher belanja di kasir secara langsung.</p>
            </div>

            <!-- ALERT MESSAGE CONTAINER -->
            <div id="action-alert" class="hidden mb-6 p-4 text-xs tracking-wider flex items-center justify-between border-l-4 shadow-sm">
                <span id="action-alert-text"></span>
                <button onclick="document.getElementById('action-alert').classList.add('hidden')" class="text-stone-400 hover:text-stone-900 font-bold ml-4">&times;</button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white border border-gray-100 p-6 shadow-sm">
                    <div id="qr-reader" class="w-full"></div>
                    <p class="text-[10px] tracking-[0.3em] uppercase text-gray-400 mt-4 text-center">Camera scanner aktif</p>
                </div>

                <div class="space-y-6">
                    <div class="bg-white border border-gray-100 p-6 shadow-sm">
                        <label for="manual_payload" class="block text-[9px] tracking-[0.3em] uppercase text-gray-400 mb-3">Input Token / Payload Manual</label>
                        <textarea id="manual_payload" rows="3" class="w-full border-gray-200 focus:border-black focus:ring-black text-xs font-mono" placeholder="Paste kode QR token pelanggan di sini..."></textarea>
                        <button id="scan-manual" class="mt-3 w-full bg-black hover:bg-stone-800 text-white text-[9px] uppercase tracking-[0.3em] font-bold py-3 transition shadow">
                            Verifikasi Member
                        </button>
                    </div>

                    <div id="scan-result" class="bg-white border border-gray-100 p-6 shadow-sm min-h-[220px]">
                        <p class="text-[9px] tracking-[0.3em] uppercase text-gray-400 mb-4">Hasil Verifikasi Member</p>
                        <p class="text-xs text-gray-400 italic">Belum ada QR code yang dipindai.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        const resultBox = document.getElementById('scan-result');
        const payloadInput = document.getElementById('manual_payload');
        const scanButton = document.getElementById('scan-manual');
        const alertBox = document.getElementById('action-alert');
        const alertText = document.getElementById('action-alert-text');

        const showAlert = (message, isSuccess = true) => {
            alertBox.className = `mb-6 p-4 text-xs tracking-wider flex items-center justify-between border-l-4 shadow-sm ${
                isSuccess ? 'bg-green-50 text-green-800 border-green-500' : 'bg-red-50 text-red-800 border-red-500'
            }`;
            alertText.textContent = message;
            alertBox.classList.remove('hidden');
        };

        const renderResult = (data, isError = false) => {
            if (isError) {
                resultBox.innerHTML = `
                    <p class="text-[9px] tracking-[0.3em] uppercase text-gray-400 mb-4">Hasil Verifikasi</p>
                    <div class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">
                        ${data}
                    </div>
                `;
                return;
            }

            // Build Vouchers HTML
            let vouchersHtml = '';
            if (data.vouchers && data.vouchers.length > 0) {
                vouchersHtml = `
                    <div class="mt-6 pt-5 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-[9px] tracking-[0.3em] uppercase font-bold text-amber-700">Voucher Aktif di Dompet Pelanggan</p>
                            <span class="text-[9px] bg-amber-100 text-amber-800 px-2 py-0.5 font-bold">${data.vouchers.length} Siap Pakai</span>
                        </div>
                        <div class="space-y-3">
                            ${data.vouchers.map(v => `
                                <div id="voucher-card-${v.id}" class="border border-stone-200 p-3 bg-stone-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[8px] font-bold uppercase tracking-wider px-1.5 py-0.5 border ${v.category_badge}">${v.category_label}</span>
                                        </div>
                                        <p class="text-xs font-bold text-black uppercase tracking-wider">${v.title}</p>
                                        <p class="font-mono text-xs text-amber-700 font-bold tracking-widest mt-0.5">${v.code}</p>
                                        ${v.discount_amount > 0 ? `<p class="text-[10px] text-green-600 font-semibold">Potongan: Rp ${Number(v.discount_amount).toLocaleString('id-ID')}</p>` : ''}
                                        <p class="text-[9px] text-gray-400">Berlaku s/d ${v.expires_at} (${v.expires_human})</p>
                                    </div>
                                    <button onclick="redeemVoucherAtCashier(${v.id}, '${v.code}')" 
                                        class="bg-black hover:bg-amber-600 text-white text-[9px] uppercase tracking-widest font-bold px-3 py-2 transition shadow whitespace-nowrap">
                                        ✓ Gunakan di Kasir
                                    </button>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            } else {
                vouchersHtml = `
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <p class="text-[9px] tracking-[0.3em] uppercase text-gray-400 mb-1">Voucher Belanja</p>
                        <p class="text-xs text-gray-400 italic">Pelanggan tidak memiliki voucher aktif saat ini.</p>
                    </div>
                `;
            }

            const tierBadgeColor = data.tier === 'Gold' ? 'bg-amber-100 text-amber-900 border-amber-300' : (data.tier === 'Silver' ? 'bg-gray-100 text-gray-800 border-gray-300' : 'bg-stone-100 text-stone-700 border-stone-200');

            resultBox.innerHTML = `
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <p class="text-[9px] tracking-[0.3em] uppercase text-gray-400">Profil Pelanggan Terverifikasi</p>
                    <span class="text-[9px] px-2.5 py-0.5 border font-bold uppercase tracking-wider ${tierBadgeColor}">
                        ${data.tier} Class
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-xs text-gray-700">
                    <div>
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider">Nama Member</p>
                        <p class="font-bold text-black text-sm mt-0.5">${data.name}</p>
                        <p class="text-[10px] text-gray-500 font-mono">${data.email}</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider">Saldo Poin</p>
                        <p class="font-bold text-black text-base mt-0.5">${Number(data.points).toLocaleString('id-ID')} <span class="text-[10px] text-amber-600">PTS</span></p>
                        <p class="text-[9px] text-red-500">Kedaluwarsa: ${data.points_expire_at || '-'}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider">Saldo Cashback</p>
                        <p class="font-bold text-stone-800 text-xs mt-0.5">Rp ${Number(data.cashback_balance).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                    </div>
                </div>

                ${vouchersHtml}
            `;
        };

        const verifyPayload = async (payload) => {
            const response = await fetch('{{ route('qr.scan.submit') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ payload }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'QR scan failed.');
            }

            renderResult(data.member);
            showAlert('Data member berhasil diverifikasi.', true);
        };

        window.redeemVoucherAtCashier = async (voucherId, voucherCode) => {
            if (!confirm(`Konfirmasi pemakaian voucher ${voucherCode} untuk transaksi belanja pelanggan saat ini?`)) {
                return;
            }

            try {
                const response = await fetch(`/qr-scan/redeem-voucher/${voucherId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Gagal menandai pemakaian voucher.');
                }

                showAlert(`Sukses! ${data.message}`, true);

                // Update voucher card to used state
                const card = document.getElementById(`voucher-card-${voucherId}`);
                if (card) {
                    card.className = 'border border-green-200 p-3 bg-green-50/50 flex justify-between items-center';
                    card.innerHTML = `
                        <div>
                            <p class="text-xs font-bold text-green-900">${data.voucher.title}</p>
                            <p class="font-mono text-xs text-green-700 font-bold">${data.voucher.code}</p>
                            <p class="text-[9px] text-green-600">✓ Telah digunakan di kasir (${data.voucher.used_at})</p>
                        </div>
                        <span class="text-[9px] font-bold uppercase tracking-wider text-green-700 bg-white border border-green-300 px-2.5 py-1">
                            ✓ Terpakai
                        </span>
                    `;
                }
            } catch (err) {
                showAlert(err.message, false);
            }
        };

        scanButton.addEventListener('click', async () => {
            try {
                await verifyPayload(payloadInput.value.trim());
            } catch (error) {
                renderResult(error.message, true);
            }
        });

        if (window.Html5QrcodeScanner) {
            const scanner = new Html5QrcodeScanner('qr-reader', {
                fps: 10,
                qrbox: { width: 240, height: 240 },
            });

            scanner.render(
                async (decodedText) => {
                    payloadInput.value = decodedText;

                    try {
                        await verifyPayload(decodedText);
                    } catch (error) {
                        renderResult(error.message, true);
                    }
                },
                () => {
                    // Silent scan errors while the camera keeps reading frames.
                }
            );
        } else {
            resultBox.innerHTML = `<p class="text-xs text-gray-500">Camera scanner is unavailable, but manual verification still works.</p>`;
        }
    </script>
</x-app-layout>