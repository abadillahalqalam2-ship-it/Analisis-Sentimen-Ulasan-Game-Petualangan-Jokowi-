// Memuat efek suara dari URL eksternal (Google Sounds)
const sendSound = new Audio('https://actions.google.com/sounds/v1/ui/click.ogg');
const receiveSound = new Audio('https://actions.google.com/sounds/v1/water/pop.ogg');

function sendMessage() {
    const input = document.getElementById('userInput');
    const text  = input.value.trim();
    if (!text) return;
 
    appendMessage(text, 'user');
    
    // Mainkan suara klik saat mengirim pesan
    sendSound.play().catch(e => console.log("Audio play diizinkan setelah interaksi pengguna"));

    input.value = '';
    input.disabled = true; // Kunci keyboard saat menunggu balasan
    
    // Hilangkan tombol pilihan dari layar agar rapi
    const qrContainer = document.getElementById('quickReplies');
    if (qrContainer) {
        qrContainer.style.display = 'none';
    }
    
    // MUNCULKAN EFEK PURA-PURA MENGETIK
    const box = document.getElementById('chatBox');
    const typingDiv = document.createElement('div');
    typingDiv.className = 'msg bot';
    typingDiv.id = 'typingIndicator';
    typingDiv.innerHTML = `<i class="fas fa-headset msg-icon"></i><div class="msg-content" style="color: #666; font-style: italic;">Mimin CS sedang mengetik... ✍️</div>`;
    box.appendChild(typingDiv);
    box.scrollTop = box.scrollHeight;

    // Kirim data ke PHP
    fetch('chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'pesan=' + encodeURIComponent(text)
    })
    .then(res => res.text())
    .then(reply => {
        // BERI JEDA 1.5 DETIK AGAR TERASA SEPERTI MANUSIA SUNGGUHAN
        setTimeout(() => {
            // Hapus teks "sedang mengetik"
            const indicator = document.getElementById('typingIndicator');
            if(indicator) indicator.remove();

            // Munculkan pesan asli & Mainkan suara notifikasi masuk
            appendMessage(reply, 'bot');
            receiveSound.play().catch(e => console.log("Audio error"));

            input.disabled = false;
            input.focus();
        }, 1500); // 1500 ms = 1.5 detik
    })
    .catch(err => {
        document.getElementById('typingIndicator').remove();
        appendMessage('Maaf Kak, server sedang sibuk 🙏.', 'bot');
        input.disabled = false;
    });
}
 
function appendMessage(text, who) {
    const box = document.getElementById('chatBox');
    const div = document.createElement('div');
    div.className = 'msg ' + who;
    
    if (who === 'bot') {
        div.innerHTML = `<i class="fas fa-headset msg-icon"></i><div class="msg-content">${text}</div>`;
    } else {
        div.innerHTML = `<div class="msg-content">${text}</div>`;
    }
    
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
}
 
document.getElementById('userInput').addEventListener('keypress', e => {
    if (e.key === 'Enter' && !e.target.disabled) sendMessage();
});

// FUNGSI UNTUK TOMBOL QUICK REPLIES
function sendQuickReply(text) {
    const input = document.getElementById('userInput');
    input.value = text;
    sendMessage();
}