// logManager.js
const LOG_CONFIG = {
    API_BASE_URL: 'datascss/',
    FETCH_DELAY: 200,
    ENDPOINTS: {
        GET_LOGS: 'get-telegram-logs.php'
    }
};

let lastChatTimestamp = null;
let lastActivityTimestamp = null;
let chatTable = null;
let activityTable = null;

function createChatTable() {
    const table = document.createElement('table');
    table.className = 'log-table';
    table.innerHTML = `
        <thead>
            <tr>
                <th>เวลา</th>
                <th>Chat ID</th>
                <th>สถานะ</th>
            </tr>
        </thead>
        <tbody></tbody>
    `;
    return table;
}

function createActivityTable() {
    const table = document.createElement('table');
    table.className = 'log-table';
    table.innerHTML = `
        <thead>
            <tr>
                <th>เวลา</th>
                <th>ระดับ</th>
                <th>กิจกรรม</th>
            </tr>
        </thead>
        <tbody></tbody>
    `;
    return table;
}

async function fetchLogs() {
    try {
        await new Promise(resolve => setTimeout(resolve, LOG_CONFIG.FETCH_DELAY));
        const url = `${LOG_CONFIG.API_BASE_URL}${LOG_CONFIG.ENDPOINTS.GET_LOGS}`;
        console.log("Fetching logs from:", url);
        const response = await fetch(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error(`ไม่สามารถดึง log ได้: ${response.status} - ${response.statusText}`);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error("❌ ข้อผิดพลาดในการดึง log:", error.message);
        return { chat_logs: [], activity_logs: [], all_sent: false };
    }
}

async function displayLogs() {
    const logContainer = document.getElementById('logContainer');
    if (!logContainer) {
        console.error("❌ ไม่พบ logContainer ใน DOM");
        return;
    }

    if (!chatTable) {
        chatTable = createChatTable();
        logContainer.appendChild(chatTable);
    }
    if (!activityTable) {
        const activityHeader = document.createElement('h3');
        activityHeader.textContent = 'Activity Log';
        logContainer.appendChild(activityHeader);
        activityTable = createActivityTable();
        logContainer.appendChild(activityTable);
    }

    const chatTbody = chatTable.querySelector('tbody');
    const activityTbody = activityTable.querySelector('tbody');
    const completeMessage = logContainer.querySelector('.complete-message');
    
    const { chat_logs, activity_logs, all_sent } = await fetchLogs();

    if (chat_logs.length > 0) {
        const latestChatTimestamp = chat_logs[0].timestamp;
        if (lastChatTimestamp === null || lastChatTimestamp !== latestChatTimestamp) {
            chatTbody.innerHTML = '';
            if (completeMessage) completeMessage.remove();
            lastChatTimestamp = latestChatTimestamp;
        }
        chat_logs.forEach(log => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${log.timestamp}</td>
                <td>${log.chat_id}</td>
                <td>${log.status}</td>
            `;
            chatTbody.appendChild(row);
        });
    } else if (chatTbody.innerHTML === '') {
        chatTbody.innerHTML = '<tr><td colspan="3">ไม่มี log ที่มี Chat ID ที่จะแสดง</td></tr>';
    }

    if (activity_logs.length > 0) {
        const latestActivityTimestamp = activity_logs[0].timestamp;
        if (lastActivityTimestamp === null || lastActivityTimestamp !== latestActivityTimestamp) {
            activityTbody.innerHTML = '';
            lastActivityTimestamp = latestActivityTimestamp;
        }
        activity_logs.forEach(log => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${log.timestamp}</td>
                <td>${log.level}</td>
                <td>${log.message}</td>
            `;
            activityTbody.appendChild(row);
        });
    } else if (activityTbody.innerHTML === '') {
        activityTbody.innerHTML = '<tr><td colspan="3">ไม่มี Activity Log ที่จะแสดง</td></tr>';
    }

    if (all_sent && chat_logs.length > 0) {
        if (!completeMessage) {
            const newCompleteMessage = document.createElement('p');
            newCompleteMessage.textContent = 'ทำการส่งข้อความทั้งหมดแล้ว';
            newCompleteMessage.className = 'complete-message';
            newCompleteMessage.style.color = 'green';
            newCompleteMessage.style.marginTop = '10px';
            logContainer.insertBefore(newCompleteMessage, activityTable);
        }
    } else if (completeMessage) {
        completeMessage.remove();
    }
}

function setupLogManager() {
    displayLogs();
    setInterval(displayLogs, 500); // 0.5 วินาที
}

document.addEventListener('DOMContentLoaded', setupLogManager);