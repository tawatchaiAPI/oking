const CONFIG = {
    API_BASE_URL: 'datascss/',
    FETCH_DELAY: 1000,
    ENDPOINTS: {
        GET_SENDMESSAGE: 'get-sendmessage.php',
        SEND_MESSAGE: 'send-message.php'
    }
};

async function fetchSendmessage() {
    try {
        await new Promise(resolve => setTimeout(resolve, CONFIG.FETCH_DELAY));
        const url = `${CONFIG.API_BASE_URL}${CONFIG.ENDPOINTS.GET_SENDMESSAGE}`;
        const response = await fetch(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error(`ไม่สามารถดึงข้อมูลได้: ${response.status} - ${response.statusText}`);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error("❌ ข้อผิดพลาด:", error.message);
        return {};
    }
}

async function sendMessage(id, topic, message, announcement_time = null, announcement_date = null) {
    let url;
    if ([
        'sendOpenOnlyAll',
        'sendCloseDeposit',
        'sendCloseWithdraw',
        'sendCloseOnlyAll',
        'sendCloseRechargeByOne',
        'sendSystemCutOff',
        'sendSystemMaintenance'
    ].includes(topic)) {
        url = `${CONFIG.API_BASE_URL}../telegram_webhook_instant.php`;
    } else if (topic === 'sendAnnouncementAlldayAuto') {
        url = `${CONFIG.API_BASE_URL}../telegram_webhook_daily.php`;
    } else if (topic === 'sendAnnouncementTimeAllday') {
        url = `${CONFIG.API_BASE_URL}../telegram_webhook_scheduled.php`;
    } else {
        console.error(`Invalid topic: ${topic}`);
        alert("Invalid topic");
        return;
    }

    try {
        const payload = { id, topic, message };
        if (announcement_time) payload.announcement_time = announcement_time;
        if (announcement_date) payload.announcement_date = announcement_date;

        console.log('Sending to:', url);
        console.log('Payload:', payload);

        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
            signal: AbortSignal.timeout(60000)
        });

        const textResponse = await response.text();
        console.log('Response:', response.status, textResponse);

        if (!response.ok) {
            throw new Error(`การส่งล้มเหลว: ${response.status} - ${response.statusText}`);
        }

        let result;
        try {
            result = JSON.parse(textResponse);
        } catch (jsonError) {
            throw new Error(`Failed to parse JSON: ${jsonError.message}, Raw response: "${textResponse}"`);
        }

        if (result.status === 'success') {
            alert(result.message); // แสดง "Announcement sent successfully"
            location.reload(); // รีเฟรชหน้าเว็บหลังกด OK
            await updateTables();
        } else {
            alert(`เกิดข้อผิดพลาด: ${result.message}`);
        }
    } catch (error) {
        console.error('❌ ข้อผิดพลาดในการส่ง:', error.message);
        alert(`เกิดข้อผิดพลาดในการส่งข้อความ: ${error.message} แต่ข้อความอาจส่งครบแล้ว`);
    }
}

async function updateTables() {
    const tbodyIds = [
        'sendOpenOnlyAll',
        'sendCloseDeposit',
        'sendCloseWithdraw',
        'sendCloseOnlyAll',
        'sendCloseRechargeByOne',
        'sendSystemCutOff',
        'sendSystemMaintenance',
        'sendAnnouncementAlldayAuto'
    ];

    const announcements = await fetchSendmessage();
    console.log('Announcements data:', announcements); // Debug

    tbodyIds.forEach(topic => {
        const tbody = document.getElementById(topic);
        if (!tbody) {
            console.error(`❌ ไม่พบ tbody id="${topic}" ใน DOM`);
            return;
        }

        tbody.innerHTML = '';
        const topicData = announcements[topic] || [];
        console.log(`Processing topic: ${topic}, Data:`, topicData); // Debug

        if (topicData.length > 0) {
            topicData.forEach(item => {
                const safeMessage = item.message || '(ไม่มีข้อความ)';
                const formattedMessage = safeMessage.replace(/\n/g, '<br>'); // ตัดบรรทัดตาม \n
                const encodedMessage = encodeURIComponent(safeMessage);
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="stacked-item">${formattedMessage}</td>
                    <td class="stacked-item action-cell ">
                        <button class="btn-send" 
                                data-id="${item.id}" 
                                data-topic="${item.topic}" 
                                data-message="${encodedMessage}">
                            Send Message
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="2">(ไม่มีข้อมูลในฐานข้อมูลสำหรับ topic นี้)</td></tr>';
        }
    });
}

// Event Listener สำหรับปุ่มส่งข้อความ (ย้ายออกจาก updateTables)
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-send');
    if (btn) {
        const id = btn.dataset.id;
        const topic = btn.dataset.topic;
        const message = decodeURIComponent(btn.dataset.message); // Decode กลับ
        console.log('Button clicked:', { id, topic, message }); // Debug

        if (confirm(`ยืนยันการส่งข้อความ "${message}"`)) {
            sendMessage(id, topic, message);
        }
    }
});

function setupTableUpdates() {
    const tbodyIds = [
        'sendOpenOnlyAll',
        'sendCloseDeposit',
        'sendCloseWithdraw',
        'sendCloseOnlyAll',
        'sendCloseRechargeByOne',
        'sendSystemCutOff',
        'sendSystemMaintenance'
    ];

    tbodyIds.forEach(id => {
        if (!document.getElementById(id)) {
            console.error(`❌ ไม่พบ tbody id="${id}" ใน DOM`);
        }
    });

    updateTables();
}

document.addEventListener('DOMContentLoaded', setupTableUpdates);