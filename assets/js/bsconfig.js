const CONFIG = {
    API_BASE_URL: 'datascss/',
    FETCH_DELAY: 1000,
    ENDPOINTS: {
        GET_GROUPS: 'get-groups.php',
        GET_BOT_SETTINGS: 'get-bot-settings.php',
        DELETE_BOT_SETTING: 'delete-bot-settings.php',
        ADD_BOT_SETTING: 'add-bot-setting.php',
        EDIT_BOT_SETTING: 'edit-bot-settings.php',
        GET_BOTS: 'get-token.php'
    }
};

// 📌 ฟังก์ชันดึงข้อมูลกลุ่ม (คืนค่า JSON)
export async function fetchGroups() {
    try {
        const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.GET_GROUPS);
        if (!response.ok) throw new Error(`ไม่สามารถดึงข้อมูลกลุ่มได้: ${response.status}`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error("❌ ข้อผิดพลาดในการดึงข้อมูลกลุ่ม:", error);
        return [];
    }
}

// 📌 ฟังก์ชันดึงข้อมูลกลุ่มสำหรับ dropdown
export async function fetchGroupsForDropdown(selectId) {
    try {
        const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.GET_GROUPS);
        if (!response.ok) throw new Error(`ไม่สามารถดึงข้อมูลกลุ่มได้: ${response.status}`);
        const data = await response.json();

        const selectElement = document.getElementById(selectId);
        if (!selectElement) {
            console.error(`❌ ไม่พบ elemento select ที่มี ID '${selectId}'`);
            return;
        }

        selectElement.innerHTML = '<option value="">-- เลือกกลุ่ม --</option>';
        data.forEach(group => {
            let option = document.createElement("option");
            option.value = group.group_id;
            option.textContent = `${group.group_name} (${group.group_id})`;
            selectElement.appendChild(option);
        });

        console.log("✅ โหลดข้อมูลกลุ่มสำเร็จ!");
    } catch (error) {
        console.error("❌ ข้อผิดพลาดในการดึงข้อมูลกลุ่ม:", error);
    }
}

// 📌 ฟังก์ชันดึงข้อมูลบอท
export async function fetchBots() {
    try {
        const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.GET_BOTS);
        if (!response.ok) throw new Error(`ไม่สามารถดึงข้อมูลบอทได้: ${response.status}`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error("❌ ข้อผิดพลาดในการดึงข้อมูลบอท:", error);
        return { status: 'error', data: [] };
    }
}

// 📌 ฟังก์ชันดึงข้อมูลการตั้งค่าบอท
export async function fetchBotSettings() {
    try {
        const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.GET_BOT_SETTINGS);
        if (!response.ok) throw new Error(`ไม่สามารถดึงข้อมูลการตั้งค่าบอทได้: ${response.status}`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error("❌ ข้อผิดพลาดในการดึงข้อมูลการตั้งค่าบอท:", error);
        return { status: 'error', data: [] };
    }
}

// 📌 ฟังก์ชันเพิ่มการตั้งค่าบอท
export async function addBotSetting(botId, groupId) {
    if (!botId || !groupId) {
        return { status: 'error', message: 'กรุณากรอก Bot ID และ Group ID ให้ครบถ้วน' };
    }
    try {
        const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.ADD_BOT_SETTING, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ bot_id: botId, group_id: groupId })
        });
        if (!response.ok) throw new Error(`ไม่สามารถเพิ่มการตั้งค่าบอทได้: ${response.status}`);
        const result = await response.json();
        console.log("✅ เพิ่มการตั้งค่าบอทสำเร็จ:", result);
        return result;
    } catch (error) {
        console.error("❌ ข้อผิดพลาดในการเพิ่มการตั้งค่าบอท:", error);
        return { status: 'error', message: error.message };
    }
}

// 📌 ฟังก์ชันลบการตั้งค่าบอท
export async function deleteBotSetting(settingId) {
    if (!settingId) {
        return { status: 'error', message: 'กรุณาระบุ ID การตั้งค่าบอท' };
    }
    try {
        const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.DELETE_BOT_SETTING, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: settingId })
        });
        if (!response.ok) throw new Error(`ไม่สามารถลบการตั้งค่าบอทได้: ${response.status}`);
        const result = await response.json();
        console.log("✅ ลบการตั้งค่าบอทสำเร็จ:", result);
        return result;
    } catch (error) {
        console.error("❌ ข้อผิดพลาดในการลบการตั้งค่าบอท:", error);
        return { status: 'error', message: error.message };
    }
}

// 📌 ฟังก์ชันแก้ไขการตั้งค่าบอท
export async function editBotSetting(settingId, botId, groupId) {
    if (!settingId || !botId || !groupId) {
        return { status: 'error', message: 'กรุณากรอกข้อมูลให้ครบถ้วน' };
    }
    try {
        const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.EDIT_BOT_SETTING, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: settingId, bot_id: botId, group_id: groupId })
        });
        if (!response.ok) throw new Error(`ไม่สามารถแก้ไขการตั้งค่าบอทได้: ${response.status}`);
        const result = await response.json();
        console.log("✅ แก้ไขการตั้งค่าบอทสำเร็จ:", result);
        return result;
    } catch (error) {
        console.error("❌ ข้อผิดพลาดในการแก้ไขการตั้งค่าบอท:", error);
        return { status: 'error', message: error.message };
    }
}

// 📌 ฟังก์ชันเติมข้อมูลใน dropdown
export async function populateDropdowns() {
    // เติมข้อมูลกลุ่มใน form และ modal
    await fetchGroupsForDropdown('groupId');
    await fetchGroupsForDropdown('editGroupId');

    // ดึงข้อมูลบอททั้งหมดและการตั้งค่าบอท
    const botsResponse = await fetchBots();
    const settingsResponse = await fetchBotSettings();

    const botSelect = document.getElementById('botId');
    const editBotSelect = document.getElementById('editBotId');

    if (!botSelect || !editBotSelect) {
        console.error("❌ ไม่พบ elemento select 'botId' หรือ 'editBotId'");
        return;
    }

    // ล้าง dropdown ก่อนเติมข้อมูลใหม่
    botSelect.innerHTML = '<option value="">-- เลือกบอท --</option>';
    editBotSelect.innerHTML = '<option value="">-- เลือกบอท --</option>';

    if (botsResponse.status === 'success' && botsResponse.data) {
        // ดึง bot_id ที่ถูกใช้แล้วจาก bot_settings
        const usedBotIds = settingsResponse.status === 'success' && settingsResponse.data
            ? settingsResponse.data.map(setting => parseInt(setting.bot_id))
            : [];

        // กรองบอทที่ยังไม่ถูกเลือกสำหรับ botId
        const availableBots = botsResponse.data.filter(bot => !usedBotIds.includes(parseInt(bot.id)));

        // เติมบอทที่ยังว่างใน botId
        availableBots.forEach(bot => {
            let option = document.createElement("option");
            option.value = bot.id;
            option.textContent = `${bot.bot_name} (${bot.id})`;
            botSelect.appendChild(option);
        });

        // เติมบอททั้งหมดใน editBotId (สำหรับแก้ไข)
        botsResponse.data.forEach(bot => {
            let option = document.createElement("option");
            option.value = bot.id;
            option.textContent = `${bot.bot_name} (${bot.id})`;
            editBotSelect.appendChild(option);
        });

        console.log("✅ โหลดข้อมูลบอทสำเร็จ!");
       // console.log("บอทที่ยังว่าง:", availableBots.map(bot => bot.bot_name));
       // console.log("บอทที่ถูกใช้แล้ว:", usedBotIds);
    }
}

// 📌 ฟังก์ชันอัปเดตตาราง
export function updateTable(data) {
    const tbody = document.getElementById('botSettingsTableBody');
    tbody.innerHTML = '';
    if (data.status === 'success' && data.data.length > 0) {
        data.data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.id}</td>
                <td>${row.bot_name}</td>
                <td>${row.group_name}</td>
                <td>
                    <button class="btn btn-sm btn-danger delete" data-id="${row.id}">ลบ</button>
                    <button class="btn btn-sm btn-primary edit" data-id="${row.id}" data-bot-id="${row.bot_id}" data-group-id="${row.group_id}">แก้ไข</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } else {
        tbody.innerHTML = '<tr><td colspan="4">ไม่พบข้อมูลการตั้งค่าบอท</td></tr>';
    }
}

// 📌 ฟังก์ชันโหลดข้อมูลตาราง
export async function loadTable() {
    const data = await fetchBotSettings();
    updateTable(data);
}

// 📌 ฟังก์ชันจัดการฟอร์มและเหตุการณ์
export function setupEventListeners() {
    const form = document.getElementById('botsettingsForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const groupId = document.getElementById('groupId').value;
            const botId = document.getElementById('botId').value;

            if (!groupId || !botId) {
                alert('กรุณาเลือก Group ID และ Bot Name');
                return;
            }

            const result = await addBotSetting(botId, groupId);
            if (result.status === 'success') {
                alert('ตั้งค่าบอทสำเร็จ!');
                form.reset();
                await loadTable();
                await populateDropdowns(); // อัปเดต dropdown ทันทีหลังเพิ่ม
            } else {
                alert('เกิดข้อผิดพลาด: ' + result.message);
            }
        });
    }

    const tbody = document.getElementById('botSettingsTableBody');
    if (tbody) {
        tbody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('delete')) {
                const settingId = e.target.getAttribute('data-id');
                if (confirm('คุณแน่ใจว่าต้องการลบการตั้งค่านี้?')) {
                    const result = await deleteBotSetting(settingId);
                    if (result.status === 'success') {
                        alert('ลบการตั้งค่าบอทสำเร็จ!');
                        await loadTable();
                        await populateDropdowns(); // อัปเดต dropdown ทันทีหลังลบ
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + result.message);
                    }
                }
            }

            if (e.target.classList.contains('edit')) {
                const settingId = e.target.getAttribute('data-id');
                const botId = e.target.getAttribute('data-bot-id');
                const groupId = e.target.getAttribute('data-group-id');

                document.getElementById('editSettingId').value = settingId;
                document.getElementById('editBotId').value = botId;
                document.getElementById('editGroupId').value = groupId;

                const modal = new bootstrap.Modal(document.getElementById('editBotSettingModal'));
                modal.show();
            }
        });
    }

    const saveEditButton = document.getElementById('saveEditBotSetting');
    if (saveEditButton) {
        saveEditButton.addEventListener('click', async () => {
            const settingId = document.getElementById('editSettingId').value;
            const botId = document.getElementById('editBotId').value;
            const groupId = document.getElementById('editGroupId').value;

            if (!settingId || !botId || !groupId) {
                alert('กรุณากรอกข้อมูลให้ครบถ้วน');
                return;
            }

            const result = await editBotSetting(settingId, botId, groupId);
            if (result.status === 'success') {
                alert('แก้ไขการตั้งค่าบอทสำเร็จ!');
                await loadTable();
                await populateDropdowns(); // อัปเดต dropdown ทันทีหลังแก้ไข
                bootstrap.Modal.getInstance(document.getElementById('editBotSettingModal')).hide();
            } else {
                alert('เกิดข้อผิดพลาด: ' + result.message);
            }
        });
    }
}

// 📌 โหลดข้อมูลเมื่อหน้าเว็บโหลด
document.addEventListener('DOMContentLoaded', async () => {
    await populateDropdowns();
    await loadTable();
    setupEventListeners();
});

export default CONFIG;