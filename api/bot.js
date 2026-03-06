const { createClient } = require('@supabase/supabase-js');

// Configuración de Supabase
const SUPABASE_URL = "https://xbzyvpcqtmyhrtgkgizm.supabase.co";
const SUPABASE_KEY = "sb_publishable_lqwGzBuvDXFT1stqUb_iDw_ta9DZlKt";

const supabase = createClient(SUPABASE_URL, SUPABASE_KEY);

module.exports = async (req, res) => {
    // Diagnóstico para peticiones GET
    if (req.method === 'GET') {
        const token = process.env.TELEGRAM_BOT_TOKEN;
        return res.status(200).send(`
            <h1>Bot Status</h1>
            <p><strong>Status:</strong> Online</p>
            <p><strong>Token Configurado:</strong> ${token ? '✅ SÍ' : '❌ NO'}</p>
            <hr>
            <p>Zona Horaria: UTC</p>
        `);
    }

    if (req.method === 'POST') {
        const { message } = req.body;
        if (!message || !message.text) return res.status(200).send('ok');

        const chatId = message.chat.id;
        const text = message.text.toLowerCase();
        const token = process.env.TELEGRAM_BOT_TOKEN;

        if (!token) return res.status(200).send('Config error');

        try {
            // Obtenemos la fecha actual en la zona horaria de México (CST/CDT)
            const now = new Date();
            const mexicoTime = new Date(now.toLocaleString("en-US", {timeZone: "America/Mexico_City"}));
            
            const monthNames = ["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];

            if (text.startsWith('/start')) {
                await sendTelegramMessage(chatId, "¡Hola! Soy el Bot de BirthdayTracker. 🎂\n\nUsa:\n/hoy - Cumpleaños de hoy\n/manana - Cumpleaños de mañana\n/mes [nombre] - Ver un mes completo");
            } 
            else if (text.startsWith('/hoy')) {
                const day = mexicoTime.getDate();
                const monthName = monthNames[mexicoTime.getMonth()];
                await handleSearch(chatId, monthName, day, "hoy");
            } 
            else if (text.startsWith('/manana')) {
                const tomorrow = new Date(mexicoTime);
                tomorrow.setDate(mexicoTime.getDate() + 1);
                const day = tomorrow.getDate();
                const monthName = monthNames[tomorrow.getMonth()];
                await handleSearch(chatId, monthName, day, "mañana");
            }
            else if (text.startsWith('/mes')) {
                const parts = text.split(' ');
                if (parts.length < 2) {
                    await sendTelegramMessage(chatId, "Especifica un mes (ej: /mes ENERO)");
                } else {
                    await handleMonthSearch(chatId, parts[1].toUpperCase());
                }
            }
        } catch (error) {
            console.error('Error:', error);
            await sendTelegramMessage(chatId, "Error al consultar la base de datos.");
        }

        return res.status(200).send('ok');
    }
    return res.status(405).send('Method Not Allowed');
};

async function handleSearch(chatId, month, day, label) {
    const { data, error } = await supabase
        .from('miembros')
        .select('nombre_completo, celular')
        .eq('mes', month)
        .eq('dia', day.toString())
        .order('nombre_completo', { ascending: true });

    if (error) throw error;
    if (!data || data.length === 0) {
        await sendTelegramMessage(chatId, `No hay cumpleaños registrados para ${label} (${day} de ${month}).`);
        return;
    }

    let response = `🎂 *Cumpleaños de ${label} (${day} de ${month}):*\n\n`;
    data.forEach(m => response += `• ${m.nombre_completo}${m.celular ? ' (' + m.celular + ')' : ''}\n`);
    await sendTelegramMessage(chatId, response);
}

async function handleMonthSearch(chatId, month) {
    const { data, error } = await supabase
        .from('miembros')
        .select('nombre_completo, dia')
        .eq('mes', month)
        .order('dia', { ascending: true })
        .order('nombre_completo', { ascending: true });

    if (error || !data || data.length === 0) {
        await sendTelegramMessage(chatId, `Error o no hay datos para el mes ${month}.`);
        return;
    }

    let response = `📅 *Cumpleaños de ${month}:*\n\n`;
    data.slice(0, 50).forEach(m => response += `• [Día ${m.dia}] ${m.nombre_completo}\n`);
    if (data.length > 50) response += `\n... y ${data.length - 50} más.`;
    await sendTelegramMessage(chatId, response);
}

async function sendTelegramMessage(chatId, text) {
    const token = process.env.TELEGRAM_BOT_TOKEN;
    const url = `https://api.telegram.org/bot${token}/sendMessage`;
    try {
        await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ chat_id: chatId, text: text, parse_mode: 'Markdown' })
        });
    } catch (e) {
        console.error('Error sending message:', e);
    }
}