# Email Configuration Guide - Loyalty Program

## Masalah Saat Ini
- Password reset email tidak terkirim
- MAIL_MAILER di `.env` adalah `log` (hanya menyimpan ke log file, tidak benar-benar dikirim)
- Belum ada SMTP configuration

## Solusi untuk Development (Lokal)

### Menggunakan Mailtrap (Rekomendasi - Gratis)

**Langkah 1: Setup Mailtrap**
1. Buka https://mailtrap.io/
2. Buat akun gratis
3. Buat project baru, misalnya "Khanaan Loyalty"
4. Di project, pilih tab "Integrations"
5. Cari dan pilih "Laravel"
6. Copy SMTP credentials yang ditampilkan

**Langkah 2: Update .env file Anda**
```env
MAIL_MAILER=smtp
MAIL_HOST=live.smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username_dari_mailtrap
MAIL_PASSWORD=your_api_token_dari_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@loyalty.local
MAIL_FROM_NAME="Khanaan Loyalty"
```

**Langkah 3: Test Email**
Jalankan di terminal:
```bash
php artisan tinker
> Mail::raw('Test email', fn($m) => $m->to('test@example.com'));
```

Buka https://mailtrap.io/inbox untuk melihat email yang terkirim.

---

## Solusi untuk Production

### Option 1: Gmail SMTP (Paling Mudah)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Khanaan Loyalty"
```

**Setup Gmail App Password:**
1. Enable 2-factor authentication di Gmail
2. Buka https://myaccount.google.com/apppasswords
3. Generate app password untuk "Mail" + "Windows"
4. Copy password, gunakan sebagai MAIL_PASSWORD di .env

### Option 2: SendGrid (Professional)
```env
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=your-sendgrid-api-key
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Khanaan Loyalty"
```

Install driver:
```bash
composer require symfony/sendgrid-mailer
```

### Option 3: AWS SES
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=ap-southeast-1
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

### Option 4: Mail Server Sendiri (VPS)
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your-server-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Khanaan Loyalty"
```

---

## Feature Email Notifications

Email akan otomatis terkirim untuk:

### Development
- Notifikasi loyalty hanya tersimpan di database (inbox notification)
- Email tidak terkirim (kecuali tipe 'success')

### Production
- ✅ Password reset
- ✅ Email verification
- ✅ Purchase claim approval
- ✅ Reward redemption
- ✅ Birthday reward (success)
- ✅ Admin notifications (claims pending)

---

## Troubleshooting

**Email masih tidak terkirim:**
1. Cek `.env` MAIL_MAILER bukan `'log'`
2. Jalankan: `php artisan config:clear`
3. Cek log: `storage/logs/laravel.log`

**SMTP Error - "Connection refused":**
- Periksa firewall VPS membolehkan port 587 atau 25
- Hubungi penyedia hosting

**Gmail "Less secure app" error:**
- Gunakan App Password, bukan password Gmail biasa
- Pastikan 2FA sudah aktif

---

## Testing Queued Emails

Jika queue digunakan (QUEUE_CONNECTION):
```bash
# Di terminal 1: Start queue worker
php artisan queue:work

# Di terminal 2: Trigger email
php artisan tinker
> Mail::to('test@example.com')->send(new YourMailable());
```

---

Pertanyaan? Edit file ini atau hubungi developer team.
