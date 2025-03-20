const CONFIG = {
    API_BASE_URL: 'datascss/', // URL ของ API
    FETCH_DELAY: 1000, // หน่วงเวลาในการดึงข้อมูล (1 วินาที)
    ENDPOINTS: {
      GET_BOTS: 'get-token.php',
      DELETE_BOT: 'delete-token.php',
      ADD_BOT: 'add-token.php',
      EDIT_BOT: 'edit_token.php',
    }
  };
  
  // 📌 ฟังก์ชันดึงข้อมูลบอท
  export async function fetchBots() {
    try {
      const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.GET_BOTS);
      if (!response.ok) throw new Error(`ไม่สามารถดึงข้อมูลบอทได้: ${response.status}`);
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("❌ ข้อผิดพลาดในการดึงข้อมูลบอท:", error);
      return [];
    }
  }
  
  // 📌 ฟังก์ชันเพิ่มบอท
  export async function addBot(botName, token) {
    if (!botName || !token) {
      return { status: 'error', message: 'กรุณากรอกชื่อบอทและโทเค็นให้ครบถ้วน' };
    }
  
    try {
      const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.ADD_BOT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=UTF-8' },
        body: JSON.stringify({ bot_name: botName, token: token })
      });
  
      if (!response.ok) throw new Error(`ไม่สามารถเพิ่มบอทได้: ${response.status}`);
      const result = await response.json();
      console.log("✅ เพิ่มบอทสำเร็จ:", result);
      return result;
    } catch (error) {
      console.error("❌ ข้อผิดพลาดในการเพิ่มบอท:", error);
      return { status: 'error', message: error.message };
    }
  }
  
  // 📌 ฟังก์ชันลบบอท
  export async function deleteBot(botId) {
    if (!botId) {
      console.error("❌ รหัสบอทไม่ถูกต้อง");
      return { status: 'error', message: 'รหัสบอทไม่ถูกต้อง' };
    }
  
    try {
      const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.DELETE_BOT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=UTF-8' },
        body: JSON.stringify({ bot_id: botId })
      });
  
      const textResponse = await response.text();
      console.log("คำตอบดิบจาก delete-token.php:", textResponse); // Debug response ดิบ
  
      if (!response.ok) {
        throw new Error(`ไม่สามารถลบบอทได้: ${response.status} ${response.statusText}`);
      }
  
      let result;
      try {
        result = JSON.parse(textResponse);
        console.log("ผลลัพธ์ที่แปลงแล้ว:", result); // Debug ผลลัพธ์ JSON
      } catch (jsonError) {
        console.error("❌ คำตอบไม่ใช่ JSON:", textResponse);
        return { status: 'error', message: `คำตอบจากเซิร์ฟเวอร์ไม่ถูกต้อง: ${textResponse}` };
      }
  
      return result;
    } catch (error) {
      console.error("❌ ข้อผิดพลาดในการลบบอท:", error);
      return { status: 'error', message: error.message };
    }
  }
  
  // 📌 ฟังก์ชันแก้ไขบอท
  export async function editBot(id, botName, token) {
    if (!id || !botName || !token) {
      return { status: 'error', message: 'กรุณากรอกข้อมูลให้ครบถ้วน' };
    }
  
    try {
      const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.EDIT_BOT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=UTF-8' },
        body: JSON.stringify({ id, bot_name: botName, token: token })
      });
  
      if (!response.ok) throw new Error(`ไม่สามารถแก้ไขบอทได้: ${response.status}`);
      const result = await response.json();
      console.log("✅ แก้ไขบอทสำเร็จ:", result);
      return result;
    } catch (error) {
      console.error("❌ ข้อผิดพลาดในการแก้ไขบอท:", error);
      return { status: 'error', message: error.message };
    }
  }
  
  // 📌 ฟังก์ชันแสดงข้อมูลในตาราง
  function updateTable(data, tableId) {
    const tableBody = document.getElementById(tableId);
    if (!tableBody) {
      console.error(`❌ ไม่พบตารางที่มี ID '${tableId}'`);
      return;
    }
  
    tableBody.innerHTML = '<tr><td colspan="4" class="text-center">กำลังโหลด...</td></tr>';
    setTimeout(() => {
      tableBody.innerHTML = "";
      if (!data || data.length === 0 || data.status === 'error') {
        tableBody.innerHTML = '<tr><td colspan="4" class="text-center">ไม่พบข้อมูลบอท</td></tr>';
        return;
      }
  
      // ถ้ามี data.data (จาก get-token.php ที่มี "data" field)
      const bots = data.data || data;
      bots.forEach(row => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${row.id}</td>
          <td>${row.bot_name}</td>
          <td>${row.token}</td>
          <td>
            <button class="btn btn-sm btn-danger delete" data-id="${row.id}">ลบ</button>
            <button class="btn btn-sm btn-primary edit" data-id="${row.id}" data-name="${row.bot_name}" data-token="${row.token}">แก้ไข</button>
          </td>
        `;
        tableBody.appendChild(tr);
      });
    }, CONFIG.FETCH_DELAY);
  }
  
  // 📌 ฟังก์ชันจัดการเหตุการณ์ตาราง
  function setupTableEventListeners(tableId) {
    const tableBody = document.getElementById(tableId);
    if (!tableBody) {
      console.error(`❌ ไม่พบตารางที่มี ID '${tableId}'`);
      return;
    }
  
    tableBody.addEventListener("click", async (event) => {
      console.log("คลิกที่:", event.target.className); // Debug การคลิก
  
      // กรณีคลิกปุ่ม "ลบ"
      if (event.target.classList.contains("delete")) {
        const botId = event.target.getAttribute("data-id");
        console.log("เริ่มลบบอท ID:", botId); // Debug
  
        if (confirm("คุณแน่ใจว่าต้องการลบบอทนี้หรือไม่?")) {
          const response = await deleteBot(botId);
          console.log("ผลลัพธ์การลบ:", response); // Debug ผลลัพธ์
  
          if (response && response.status === 'success') {
            alert("✅ ลบบอทสำเร็จ!");
            fetchBotsForTable(tableId);
          } else {
            const errorMessage = response && response.message ? response.message : 'เกิดข้อผิดพลาดไม่ทราบสาเหตุ';
            alert(`❌ ไม่สามารถลบบอทได้: ${errorMessage}`);
          }
        } else {
          console.log("ยกเลิกการลบ");
        }
      }
  
      // กรณีคลิกปุ่ม "แก้ไข"
      if (event.target.classList.contains("edit")) {
        const botId = event.target.getAttribute("data-id");
        const currentName = event.target.getAttribute("data-name");
        const currentToken = event.target.getAttribute("data-token");
  
        // เติมข้อมูลลงใน modal
        document.getElementById("editBotId").value = botId;
        document.getElementById("editBotName").value = currentName;
        document.getElementById("editBotToken").value = currentToken;
  
        // แสดง modal
        const modal = new bootstrap.Modal(document.getElementById("editBotModal"));
        modal.show();
      }
    });
  
    // จัดการการบันทึกจาก modal
    const saveButton = document.getElementById("saveEditBot");
    if (saveButton) {
      saveButton.addEventListener("click", async () => {
        const botId = document.getElementById("editBotId").value;
        const botName = document.getElementById("editBotName").value.trim();
        const token = document.getElementById("editBotToken").value.trim();
  
        if (!botName || !token) {
          alert("❌ กรุณากรอกชื่อบอทและโทเค็นให้ครบถ้วน");
          return;
        }
  
        const response = await editBot(botId, botName, token);
        if (response.status === 'success') {
          alert("✅ แก้ไขบอทสำเร็จ!");
          fetchBotsForTable(tableId);
          bootstrap.Modal.getInstance(document.getElementById("editBotModal")).hide();
        } else {
          alert(`❌ ไม่สามารถแก้ไขบอทได้: ${response.message || 'เกิดข้อผิดพลาดไม่ทราบสาเหตุ'}`);
        }
      });
    }
  }
  
  // 📌 ฟังก์ชันดึงข้อมูลและแสดงผลในตาราง
  async function fetchBotsForTable(tableId) {
    try {
      const data = await fetchBots();
      updateTable(data, tableId);
    } catch (error) {
      console.error(`❌ ข้อผิดพลาดในการดึงข้อมูลสำหรับ ${tableId}:`, error);
    }
  }
  
  // 📌 เมื่อโหลดหน้าเว็บ
  document.addEventListener("DOMContentLoaded", () => {
    const tableId = "botTableBody";
    fetchBotsForTable(tableId);
    setupTableEventListeners(tableId);
  
    const form = document.getElementById('addBotForm');
    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
  
        const botName = document.getElementById('botName').value.trim();
        const token = document.getElementById('botToken').value.trim();
  
        if (!botName || !token) {
          alert("❌ กรุณากรอกชื่อบอทและโทเค็นให้ครบถ้วน");
          return;
        }
  
        const response = await addBot(botName, token);
        if (response.status === 'success') {
          alert('✅ เพิ่มบอทสำเร็จ!');
          form.reset();
          fetchBotsForTable(tableId);
        } else {
          alert(`❌ ไม่สามารถเพิ่มบอทได้: ${response.message || 'เกิดข้อผิดพลาดไม่ทราบสาเหตุ'}`);
        }
      });
    } else {
      console.error("❌ ไม่พบฟอร์ม 'addBotForm'");
    }
  });
  
  export default CONFIG;