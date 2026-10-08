<?php
header('Content-Type: text/plain; charset=utf-8');
 
$pesan = isset($_POST['pesan']) ? trim($_POST['pesan']) : '';
$pesan_lower = strtolower($pesan);

if (empty($pesan)) {
    echo "Pesan tidak boleh kosong.";
    exit;
}

// ==========================================
// 1. INTEGRASI API SENTIMEN (FLASK ML)
// ==========================================
if (strpos($pesan_lower, 'sentimen') !== false || strpos($pesan_lower, 'analisis') !== false) {
    $teks = trim(str_replace(['sentimen', 'analisis', 'coba', ':'], '', $pesan_lower));
    
    if ($teks) {
        $ch = curl_init('http://127.0.0.1:5000/predict');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['teks' => $teks]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        
        $resp = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200 && $resp) {
            $r = json_decode($resp, true);
            if ($r && isset($r['sentimen'])) {
                $sentiment = strtoupper($r['sentimen']);
                $emoji = ($sentiment == 'POSITIF') ? '✅' : (($sentiment == 'NEGATIF') ? '❌' : '➖');
                echo "Hasil Prediksi Model SVM:\n\nTeks: \"".$r['teks_clean']."\"\nSentimen: <strong>" . $sentiment . "</strong> " . $emoji;
                exit;
            }
        } else {
            echo "Maaf Kak, mesin analitik kami sedang istirahat sebentar (Flask tidak aktif). Pastikan server Python sudah menyala ya.";
            exit;
        }
    } else {
        echo "Silakan ketik kalimat ulasan game yang ingin Kakak tes setelah kata 'sentimen'. Contoh: 'analisis sentimen: gamenya asyik banget bikin nagih!'";
        exit;
    }
}

// ==========================================
// 2. RULE-BASED INTENTS (Persona Customer Service PRO)
// ==========================================
$intents = [
    'salam' => [
        'patterns'  => ['halo', 'hai', 'hello', 'pagi', 'siang', 'malam', 'ping', 'min', 'mimin', 'oy', 'p', 'hy', 'assalamualaikum', 'samlekom', 'punten'],
        'responses' => [
            'Halo Kak! 👋 Selamat datang di Pusat Layanan Pelanggan Game Petualangan Jokowi. Saya Mimin CS yang siap membantu menjawab pertanyaan atau mencatat keluhan Kakak hari ini. Ada yang bisa dibantu?',
            'Hai Kak! Terima kasih sudah menghubungi layanan *support* kami. Jangan sungkan, silakan sampaikan pertanyaan atau kendala apa pun yang Kakak alami saat bermain 😊.'
        ]
    ],
    'terima_kasih' => [
        'patterns'  => ['terima kasih', 'makasih', 'maksih', 'mksh', 'thanks', 'thx', 'tq', 'thank you', 'suwun', 'nuhun', 'mantap', 'oke sip', 'baik min'],
        'responses' => [
            'Sama-sama, Kak! 🥰 Senang sekali Mimin bisa membantu. Kalau ada kendala atau pertanyaan lain ke depannya, jangan ragu untuk chat Mimin lagi ya. Selamat bermain!',
            'Kembali kasih, Kak! 🙏 Terima kasih juga atas dukungan Kakak untuk game Petualangan Jokowi. Semoga hari Kakak menyenangkan dan selalu hoki saat main gamenya!',
            'Dengan senang hati, Kak! 😉 Selamat melanjutkan aktivitas dan semoga *gameplay* Kakak makin jago ya!'
        ]
    ],
    'easter_egg' => [
        // FITUR BARU: Mencegah pemain yang iseng menanyakan Cheat
        'patterns'  => ['cheat', 'hesoyam', 'aezakmi', 'cit', 'kode rahasia', 'mod apk', 'mod', 'hacker', 'cit kebal', 'uang tak terbatas'],
        'responses' => [
            'Waduh, ketahuan nih Kakak mau coba nge-cheat ya? 🤣 Di Petualangan Jokowi kita main jujur pakai skill dong, Kak! Yuk semangat lewatin rintangannya tanpa jalan pintas! 🛡️',
            'Eits, Mimin CS mantau lho! 🧐 Gak ada cheat atau mod apk ya Kak di sini. Kita buktikan kalau gamer Indonesia itu jago-jago tanpa aplikasi curang!'
        ]
    ],
    'tentang_game' => [
        'patterns'  => ['game apa', 'tentang', 'petualangan jokowi', 'ceritanya', 'tujuan', 'maksud', 'game ini', 'alur', 'misi', 'ngapain', 'ngapain aja'],
        'responses' => [
            'Game "Petualangan Jokowi" adalah permainan kasual (*casual game*) seru karya anak bangsa! Di sini Kakak akan bermain melewati berbagai rintangan dan *stage* yang menantang. Tujuannya mengumpulkan skor tertinggi dan mengalahkan bos di setiap level. Seru banget buat ngisi waktu luang loh, Kak! 🎮'
        ]
    ],
    'karakter' => [
        'patterns'  => ['karakter', 'prabowo', 'anies', 'gibran', 'hero', 'skin', 'ganti', 'zilong', 'alok', 'nambah', 'tambah tokoh', 'char', 'charnya', 'buka', 'wowo', 'bisa ganti'],
        'responses' => [
            'Wah, ide yang bagus banget Kak! ✨ Saat ini kami memang baru menyediakan karakter utama Pak Jokowi. Namun, antusiasme Kakak untuk menambahkan karakter/skin baru (seperti tokoh populer lainnya) sudah saya catat dan teruskan ke tim Developer. Pantau terus *update* kami ya, semoga segera direalisasikan!'
        ]
    ],
    'gameplay' => [
        'patterns'  => ['cara main', 'cara bermain', 'gimana main', 'gimana cara main', 'tombol', 'susah', 'tutorial', 'analog', 'level', 'bos', 'musuh', 'ngendaliin', 'kontrol', 'tutor', 'cara gerak', 'stuck', 'cara menang', 'pusing', 'gak ngerti'],
        'responses' => [
            'Tenang Kak, cara mainnya cukup mudah! 🕹️ Kakak tinggal menggunakan *joystick virtual* (analog) di layar untuk bergerak dan menghindari rintangan/musuh. Semakin tinggi levelnya, musuhnya memang dirancang agar lebih gesit dan menantang. Jangan menyerah, terus asah *skill* Kakak ya! Pasti bisa lewat kok! 💪'
        ]
    ],
    'bug_dan_masalah' => [
        'patterns'  => ['bug', 'lag', 'lemot', 'error', 'hitam', 'keluar sendiri', 'tembus', 'layar', 'burik', 'macet', 'nyangkut', 'jelek', 'patah', 'ngebug', 'ngelag', 'fc', 'force close', 'mati sendiri', 'panas', 'ngeselin', 'kecewa'],
        'responses' => [
            'Mohon maaf sekali atas ketidaknyamanan yang Kakak rasakan 🙏. Kami mengerti hal ini sangat mengganggu (*bug*, layar hitam, *lag*, atau *force close*). Laporan kendala ini sudah langsung saya sampaikan ke tim teknis agar segera diperbaiki. Sambil menunggu perbaikan, Kakak bisa coba bersihkan *cache* aplikasi gamenya dan pastikan koneksi stabil ya. Terima kasih banyak atas kesabarannya, Kak!'
        ]
    ],
    'iklan_dan_kuota' => [
        'patterns'  => ['iklan', 'ads', 'offline', 'online', 'kuota', 'nonton iklan', 'internet', 'top up', 'beli', 'bayar'],
        'responses' => [
            'Terkait iklan dan fitur dalam game: game ini sepenuhnya GRATIS untuk dimainkan ya Kak! Beberapa fitur (seperti klaim bonus) mungkin memerlukan koneksi internet untuk menonton iklan pendukung. Kalau tombol iklannya tidak muncul, coba pastikan koneksi internet Kakak stabil atau *restart* gamenya sebentar. 🌐'
        ]
    ],
    'update' => [
        'patterns'  => ['update', 'perbarui', 'kapan', 'abdet', 'tambahin', 'versi', 'rilis', 'patch', 'maintenance', 'benerin', 'perbaikan'],
        'responses' => [
            'Halo Kak! Tim Developer kami saat ini sedang bekerja keras meracik *update* versi terbaru agar *gameplay* makin lancar dan minim *bug*. Pastikan fitur *auto-update* di Google Play Store Kakak sudah menyala ya, biar Kakak jadi yang pertama tahu kalau ada pembaruan! 🚀'
        ]
    ],
    'bantuan' => [
        'patterns'  => ['bantuan', 'tolong', 'help', 'menu', 'bisa apa', 'tanya', 'nanya', 'mau nanya', 'bingung', 'panduan', 'info', 'pertanyaan lainnya', 'lainnya'],
        'responses' => [
            'Dengan senang hati, Kak! Mimin CS siap membantu. Kakak bisa nanya soal: cara bermain (*gameplay*), info karakter, masalah/keluhan (*bug/lag*), info *offline/iklan*, atau jadwal *update*. Silakan ketik apa yang bikin Kakak bingung.'
        ]
    ]
];
 
$default = "Mohon maaf Kak, Mimin CS belum berhasil menangkap maksud pertanyaan tersebut 😥. Boleh coba gunakan kata kunci lain? Kakak bisa ketik tentang 'cara main', lapor 'ngebug/lag', tanya 'iklan/offline', request 'karakter baru', atau 'info update'.";
 
$balasan = $default;
foreach ($intents as $intent => $data) {
    foreach ($data['patterns'] as $pat) {
        if (strpos($pesan_lower, $pat) !== false) {
            $balasan = $data['responses'][array_rand($data['responses'])];
            break 2;
        }
    }
}
 
echo $balasan;