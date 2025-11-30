process.env.DEBUG = 'whatsapp-web.js:*'; // Debug detail WA WebJS

const { Pool } = require('pg');
const { Client, LocalAuth } = require("whatsapp-web.js");
const qrcode = require("qrcode-terminal");
const fs = require("fs");
const path = require("path");

// =====================
// KONFIGURASI DATABASE
// =====================
const pool = new Pool({
    host: 'localhost',
    port: 5432, 
    database: 'ticket',
    user: 'postgres',
    password: 'root',
});


// =====================
// KONFIGURASI CLIENT WA
// =====================
const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
    },
});

// =====================
// LISTENER QR CODE
// =====================
client.on('qr', (qr) => {
    console.log('📱 QR RECEIVED, Scan untuk login...');
    qrcode.generate(qr, { small: true });
});

// =====================
// DEBUGGING LIFECYCLE
// =====================
client.on('loading_screen', (percent, message) => {
    console.log('⌛ Loading screen', percent, message);
});

client.on('authenticated', () => {
    console.log('✅ Authenticated!');
    // Auto reset jika tidak ada "ready" dalam 30 detik
    setTimeout(() => {
        if (!client.info) {
            console.error("⚠️ Tidak pernah masuk ke READY, hapus session & restart...");
            try {
                const sessionPath = path.join(process.cwd(), '.wwebjs_auth');
                fs.rmSync(sessionPath, { recursive: true, force: true });
                console.log("🗑 Session dihapus. Restart server manual dan scan ulang QR.");
                process.exit(1);
            } catch (e) {
                console.error("Gagal hapus session:", e.message);
            }
        }
    }, 30000);
});

client.on('auth_failure', (msg) => {
    console.error('❌ Auth Failure:', msg);
});

client.on('change_state', state => {
    console.log('🔄 State changed to:', state);
});

client.on('disconnected', (reason) => {
    console.error('❌ Disconnected:', reason);
});

// =====================
// SAAT CLIENT READY
// =====================
client.on('ready', async () => {
    console.log('✅ Client is ready to consume data!');
    startConsumerLoop();
});

// =====================
// LOOP CONSUME DATA
// =====================
let isProcessing = false;
function startConsumerLoop() {
    setInterval(async () => {
        if (isProcessing) return;
        isProcessing = true;

        const item = await consumeQueueWhatsapp(); // ambil 1 antrian

        if (!item) {
            isProcessing = false;
            return;
        }

        const phoneNumber = item.phone;
        const chatId = phoneNumber + "@c.us";

        try {
            const isRegistered = await client.isRegisteredUser(chatId);
            if (!isRegistered) {
                console.log(`❌ Nomor ${rawNumber} tidak terdaftar di WhatsApp`);
                await deleteQueueWhatsapp(item.id);
                isProcessing = false;
                return;
            }

            try {
                const sent = await client.sendMessage(chatId, item.message);
                console.log(`✅ Pesan terkirim ke ${phoneNumber}`);

                await deleteQueueWhatsapp(item.id);
                await new Promise(resolve => setTimeout(resolve, 1000));
            } catch (error) {
                console.error(`❌ Gagal kirim ke ${phoneNumber}: ${error.message}`);

                if (error.message.includes('serialize')) {
                    console.warn(`⚠️ Kirim berhasil tapi error serialize, hapus queue manual...`);
                    await deleteQueueWhatsapp(item.id);
                }
            }
        } catch (error) {
            console.error(`❌ Gagal kirim ke ${phoneNumber}: ${error.message}`);
        }

        isProcessing = false;
    }, 2000);
}


// =====================
// FUNGSI HELPER
// =====================


async function consumeQueueWhatsapp() {
    const resultQuery = await pool.query('SELECT * FROM queue_whatsapps ORDER BY id ASC');
    const data = resultQuery.rows;

    return data;
}

async function deleteQueueWhatsapp(id) {
    await pool.query('DELETE FROM queue_whatsapps WHERE id = $1', [id]);
}

// =====================
// INISIALISASI CLIENT
// =====================
client.initialize();
