const CONFIG = {
  API_BASE_URL: 'datascss/',
  FETCH_DELAY: 1000,
  ENDPOINTS: {
    GET_GROUPS: 'get-groups.php',
    SAVE_ANNOUNCEMENT: 'save-announcement.php',
    GET_ANNOUNCEMENTS: 'get-announcements.php',
    DELETE_ANNOUNCEMENT: 'delete-announcement.php',
    UPDATE_ANNOUNCEMENT: 'edit-announcement.php'
  }
};

// 📌 ฟังก์ชันดึงข้อมูลกลุ่ม
async function fetchGroupsForDropdown(selectId) {
  const selectElement = document.getElementById(selectId);
  if (!selectElement) {
    console.error(`❌ ไม่พบ element select ที่มี ID '${selectId}'`);
    return;
  }

  try {
    const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.GET_GROUPS);
    if (!response.ok) throw new Error(`ไม่สามารถดึงข้อมูลกลุ่มได้: ${response.status}`);
    const data = await response.json();

    selectElement.innerHTML = '<option value="">-- Select Group --</option>';
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

// 📌 ฟังก์ชันบันทึกประกาศ
async function saveAnnouncement(group_id, message, topic, announcement_time = null, announcement_date = null) {
  if (!group_id || !topic) {
    console.error("❌ ข้อมูลไม่ครบถ้วน:", { group_id, message, topic, announcement_time, announcement_date });
    return { status: 'error', message: "กรุณากรอกกลุ่มและหัวข้อให้ครบถ้วน" };
  }

  const payload = { group_id, message, topic };
  if (announcement_time) payload.announcement_time = announcement_time;
  if (announcement_date) payload.announcement_date = announcement_date;

  const fullUrl = CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.SAVE_ANNOUNCEMENT;
  console.log("📤 ข้อมูลที่ส่งไป:", payload);
  console.log("🌐 URL ที่ใช้:", fullUrl);

  try {
    const response = await fetch(fullUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json; charset=UTF-8' },
      body: JSON.stringify(payload)
    });

    const responseText = await response.text();
    console.log("📥 Response ดิบจาก API:", responseText);

    let responseData;
    try {
      responseData = JSON.parse(responseText);
    } catch (jsonError) {
      throw new Error(`Failed to parse JSON: ${jsonError.message}, Raw response: "${responseText}"`);
    }

    console.log("📥 Response JSON จาก API:", responseData);

    if (!response.ok) throw new Error(`ไม่สามารถบันทึกได้: ${responseData.message || response.status}`);
    return responseData;
  } catch (error) {
    console.error("❌ ข้อผิดพลาดในการส่งข้อมูล:", error);
    return { status: 'error', message: error.message };
  }
}

// 📌 ฟังก์ชันดึงข้อมูลประกาศทั้งหมด
async function fetchAnnouncements() {
  const fullUrl = CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.GET_ANNOUNCEMENTS;
 // console.log("🌐 URL ที่ใช้สำหรับดึงข้อมูล:", fullUrl);

  try {
    const response = await fetch(fullUrl);
    if (!response.ok) throw new Error(`ไม่สามารถดึงข้อมูลประกาศได้: ${response.status}`);

    const responseText = await response.text();
   // console.log("📥 Response ดิบจาก API:", responseText);

    let data;
    try {
      data = JSON.parse(responseText);
    } catch (jsonError) {
      throw new Error(`Failed to parse JSON: ${jsonError.message}, Raw response: "${responseText}"`);
    }

    if (!Array.isArray(data)) throw new Error("ข้อมูลประกาศไม่ถูกต้อง");
    //console.log("📋 ข้อมูลทั้งหมดที่ดึงมา:", data);
    return data;
  } catch (error) {
    console.error("❌ ข้อผิดพลาดในการดึงข้อมูลประกาศ:", error);
    return [];
  }
}

// 📌 ฟังก์ชันลบประกาศ
async function deleteAnnouncement(announcementId, topic) {
  const fullUrl = CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.DELETE_ANNOUNCEMENT;
  console.log("🌐 URL ที่ใช้สำหรับลบ:", fullUrl);
  console.log("📤 ข้อมูลที่ส่งไป:", { announcement_id: announcementId, topic });

  try {
    const response = await fetch(fullUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json; charset=UTF-8' },
      body: JSON.stringify({ announcement_id: announcementId, topic })
    });

    console.log("📥 HTTP Status:", response.status, response.statusText);

    const responseText = await response.text();
    console.log("📥 Response ดิบจาก API:", responseText);

    let responseData;
    if (responseText.trim() === "") {
      if (response.ok) {
        responseData = { status: "success", message: "Deleted successfully (empty response)" };
      } else {
        throw new Error(`ไม่สามารถลบได้: Empty response with status ${response.status}`);
      }
    } else {
      try {
        responseData = JSON.parse(responseText);
      } catch (jsonError) {
        throw new Error(`Failed to parse JSON: ${jsonError.message}, Status: ${response.status}, Raw response: "${responseText}"`);
      }
    }

    console.log("📥 Response JSON จาก API:", responseData);

    if (!response.ok) throw new Error(`ไม่สามารถลบได้: ${responseData.message || response.status}`);
    return responseData;
  } catch (error) {
    console.error("❌ ข้อผิดพลาดในการลบประกาศ:", error);
    return { status: 'error', message: error.message };
  }
}

// 📌 ฟังก์ชันแก้ไขประกาศ
async function editAnnouncement(id, message, topic, announcement_time = null, announcement_date = null) {
  const payload = { id, message, topic };
  if (announcement_time) payload.announcement_time = announcement_time;
  if (announcement_date) payload.announcement_date = announcement_date;

  const fullUrl = CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.UPDATE_ANNOUNCEMENT;
  console.log("🌐 URL ที่ใช้สำหรับแก้ไข:", fullUrl);
  console.log("📤 ข้อมูลที่ส่งไป:", payload);

  try {
    const response = await fetch(fullUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json; charset=UTF-8' },
      body: JSON.stringify(payload)
    });

    const responseText = await response.text();
    console.log("📥 Response ดิบจาก API:", responseText);

    let responseData;
    try {
      responseData = JSON.parse(responseText);
    } catch (jsonError) {
      throw new Error(`Failed to parse JSON: ${jsonError.message}, Raw response: "${responseText}"`);
    }

    console.log("📥 Response JSON จาก API:", responseData);

    if (!response.ok) throw new Error(`ไม่สามารถแก้ไขได้: ${responseData.message || response.status}`);
    return responseData;
  } catch (error) {
    console.error("❌ ข้อผิดพลาดในการแก้ไขประกาศ:", error);
    return { status: 'error', message: error.message };
  }
}

// 📌 ฟังก์ชันอัปเดตตารางประกาศ
async function updateAnnouncementTable() {
  const tableBodies = {
    auto: document.getElementById("autoMessageTableBody"),
    time: document.getElementById("timeMessageTableBody"),
    day: document.getElementById("dayMessageTableBody")
  };

  let allTablesExist = true;
  for (const [key, tbody] of Object.entries(tableBodies)) {
    if (!tbody) {
      console.warn(`⚠️ ไม่พบ tbody สำหรับ '${key}MessageTableBody' ในหน้านี้`);
      allTablesExist = false;
    } else {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center">กำลังโหลด...</td></tr>';
    }
  }

  if (!allTablesExist) console.log("ℹ️ ดำเนินการต่อโดยใช้เฉพาะตารางที่มีอยู่");

  const announcements = await fetchAnnouncements();

  for (const tbody of Object.values(tableBodies)) {
    if (tbody) tbody.innerHTML = "";
  }

  if (announcements.length === 0) {
    for (const tbody of Object.values(tableBodies)) {
      if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center">ไม่พบประกาศ</td></tr>';
    }
    console.warn("⚠️ ไม่มีข้อมูลประกาศจาก API");
    return;
  }

  const createRow = (announcement) => {
    return `
      <tr>
        <td>${announcement.message || '-'}</td>
        <td>${announcement.topic || '-'}</td>
        <td>
          <button class="btn btn-sm btn-primary edit-announcement" 
                  data-id="${announcement.id}" 
                  data-message="${announcement.message || ''}" 
                  data-topic="${announcement.topic || ''}"
                  data-time="${announcement.announcement_time || ''}"
                  data-date="${announcement.announcement_date || ''}">แก้ไข</button>
          <button class="btn btn-sm btn-danger delete-announcement" 
                  data-id="${announcement.id}" 
                  data-topic="${announcement.topic || ''}">ลบ</button>
        </td>
      </tr>
    `;
  };

  const autoTopics = [
    'sendOpenOnlyAll',
    'sendCloseDeposit',
    'sendCloseWithdraw',
    'sendCloseOnlyAll',
    'sendCloseRechargeByOne',
    'sendSystemCutOff',
    'sendSystemMaintenance',
    'sendNotificationAnnouncement',
    'sendCutoff',
    'sendOpen',
    'sendOpenD',
    'sendOpenW',
    'sendClose',
    'sendClose_Deposit',
    'sendClose_Withdraw',
    'sendAnnounceDeposit',
    'sendAnnounceWithdraw'
  ];
  const timeTopics = ['sendAnnouncementAlldayAuto'];
  const dayTopics = ['sendAnnouncementTimeAllday'];

  announcements.forEach(announcement => {
    const tr = document.createElement("tr");
    tr.innerHTML = createRow(announcement);

    const topic = announcement.topic || '';
    let targetTbody;

    if (timeTopics.includes(topic)) {
      targetTbody = tableBodies.time;
      console.log(`✅ จัดข้อมูล topic '${topic}' ไปที่ timeMessageTableBody`);
    } else if (autoTopics.includes(topic)) {
      targetTbody = tableBodies.auto;
      console.log(`✅ จัดข้อมูล topic '${topic}' ไปที่ autoMessageTableBody`);
    } else if (dayTopics.includes(topic)) {
      targetTbody = tableBodies.day;
      console.log(`✅ จัดข้อมูล topic '${topic}' ไปที่ dayMessageTableBody`);
    } else {
      console.warn(`⚠️ Topic '${topic}' ไม่ตรงกับเงื่อนไขใด ๆ`);
    }

    if (targetTbody) {
      targetTbody.appendChild(tr);
    }
  });

  for (const [key, tbody] of Object.entries(tableBodies)) {
    if (tbody && tbody.children.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center">ไม่พบข้อมูลสำหรับ ${key}</td></tr>`;
      console.log(`ℹ️ ไม่มีข้อมูลใน ${key}MessageTableBody`);
    }
  }

  console.log("✅ โหลดและกระจายประกาศสำเร็จ!");
}

// 📌 การจัดการเหตุการณ์ตาราง
function setupTableEventListeners() {
  const tableBodyIds = ["autoMessageTableBody", "timeMessageTableBody", "dayMessageTableBody"];
  
  tableBodyIds.forEach(id => {
    const tableBody = document.getElementById(id);
    if (!tableBody) {
      console.warn(`⚠️ ไม่พบตาราง '${id}' สำหรับ event listeners ในหน้านี้`);
      return;
    }

    tableBody.addEventListener("click", async (event) => {
      if (event.target.classList.contains("edit-announcement")) {
        const announcementId = event.target.getAttribute("data-id");
        const currentMessage = event.target.getAttribute("data-message");
        const currentTopic = event.target.getAttribute("data-topic");
        const currentTime = event.target.getAttribute("data-time");
        const currentDate = event.target.getAttribute("data-date");

        const elements = {
          editIdEl: document.getElementById("editAnnouncementId"),
          editSourceTableEl: document.getElementById("editSourceTable"),
          editMessageEl: document.getElementById("editMessage"),
          editTopicEl: document.getElementById("editTopic"),
          editTimeEl: document.getElementById("editAnnouncementTime"),
          editDateEl: document.getElementById("editAnnouncementDate"),
          modalEl: document.getElementById("editAnnouncementModal")
        };

        for (const [key, el] of Object.entries(elements)) {
          if (!el) {
            console.error(`❌ ไม่พบ element '${key}' (ID: ${key.replace('El', '')}) ใน modal`);
            return;
          }
        }

        // กำหนดค่าเริ่มต้น
        elements.editIdEl.value = announcementId;
        elements.editSourceTableEl.value = id; // เก็บว่าแก้ไขจาก <tbody> ไหน
        elements.editMessageEl.value = currentMessage;
        elements.editTopicEl.value = currentTopic;
        elements.editTimeEl.value = currentTime || '';
        elements.editDateEl.value = currentDate || '';

        // แสดง/ซ่อนฟิลด์ตาม <tbody>
        const timeField = document.getElementById("timeField");
        const dateField = document.getElementById("dateField");
        if (id === "autoMessageTableBody") {
          timeField.style.display = "none";
          dateField.style.display = "none";
        } else if (id === "timeMessageTableBody") {
          timeField.style.display = "block";
          dateField.style.display = "none";
        } else if (id === "dayMessageTableBody") {
          timeField.style.display = "block";
          dateField.style.display = "block";
        }

        const modal = new bootstrap.Modal(elements.modalEl);
        modal.show();
      }

      if (event.target.classList.contains("delete-announcement")) {
        const announcementId = event.target.getAttribute("data-id");
        const topic = event.target.getAttribute("data-topic");
        if (confirm("คุณแน่ใจว่าต้องการลบข้อความนี้หรือไม่?")) {
          const response = await deleteAnnouncement(announcementId, topic);
          if (response.status === "success") {
            alert("✅ ลบข้อความสำเร็จ!");
            updateAnnouncementTable();
          } else {
            alert(`❌ ไม่สามารถลบได้: ${response.message}`);
          }
        }
      }
    });
  });

  const saveButton = document.getElementById("saveEditAnnouncement");
  if (saveButton) {
    saveButton.addEventListener("click", async () => {
      const elements = {
        announcementId: document.getElementById("editAnnouncementId"),
        sourceTable: document.getElementById("editSourceTable"),
        message: document.getElementById("editMessage"),
        topic: document.getElementById("editTopic"),
        announcementTime: document.getElementById("editAnnouncementTime"),
        announcementDate: document.getElementById("editAnnouncementDate")
      };

      for (const [key, el] of Object.entries(elements)) {
        if (!el) {
          console.error(`❌ ไม่พบ element '${key}' (ID: edit${key}) ใน modal`);
          return;
        }
      }

      const announcementId = elements.announcementId.value;
      const sourceTable = elements.sourceTable.value;
      const message = elements.message.value.trim();
      const topic = elements.topic.value;
      const announcementTime = sourceTable !== "autoMessageTableBody" ? elements.announcementTime.value || null : null;
      const announcementDate = sourceTable === "dayMessageTableBody" ? elements.announcementDate.value || null : null;

      if (!announcementId || !message || !topic) {
        alert("❌ กรุณากรอกข้อมูลให้ครบถ้วน");
        return;
      }

      const response = await editAnnouncement(announcementId, message, topic, announcementTime, announcementDate);
      if (response.status === "success") {
        alert("✅ แก้ไขข้อความสำเร็จ!");
        updateAnnouncementTable();
        bootstrap.Modal.getInstance(document.getElementById("editAnnouncementModal")).hide();
      } else {
        alert(`❌ ไม่สามารถแก้ไขได้: ${response.message || 'เกิดข้อผิดพลาด'}`);
      }
    });
  } else {
    console.warn("⚠️ ไม่พบปุ่ม saveEditAnnouncement");
  }
}

// 📌 เมื่อโหลดหน้าเว็บ
document.addEventListener("DOMContentLoaded", () => {
  fetchGroupsForDropdown("groupId");
  updateAnnouncementTable();
  setupTableEventListeners();

  const form = document.getElementById('announcementForm');
  const feedback = document.getElementById('formFeedback');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const groupId = document.getElementById('groupId').value;
      const message = document.getElementById('message').value.trim();
      const topic = document.getElementById('topic').value;

      if (!groupId || !topic) {
        if (feedback) feedback.innerHTML = '<div class="alert alert-danger">❌ กรุณากรอกกลุ่มและหัวข้อให้ครบถ้วน</div>';
        return;
      }

      const response = await saveAnnouncement(groupId, message, topic);
      if (response.status === 'success') {
        if (feedback) feedback.innerHTML = '<div class="alert alert-success">✅ บันทึกประกาศสำเร็จ!</div>';
        form.reset();
        updateAnnouncementTable();
        setTimeout(() => { if (feedback) feedback.innerHTML = ''; }, 3000);
      } else {
        if (feedback) feedback.innerHTML = `<div class="alert alert-danger">❌ ไม่สามารถบันทึกได้: ${response.message}</div>`;
      }
    });
  }

  const formTime = document.getElementById('announcementFormTime');
  const feedbackTime = document.getElementById('formFeedbackTime');
  if (formTime) {
    formTime.addEventListener('submit', async (e) => {
      e.preventDefault();

      const groupId = document.getElementById('groupId').value;
      const message = document.getElementById('message').value.trim();
      const topic = document.getElementById('topic').value;
      const announcementTime = document.getElementById('announcementTime').value;

      if (!groupId || !topic || !announcementTime) {
        if (feedbackTime) feedbackTime.innerHTML = '<div class="alert alert-danger">❌ กรุณากรอกข้อมูลให้ครบถ้วน</div>';
        return;
      }

      const response = await saveAnnouncement(groupId, message, topic, announcementTime);
      if (response.status === 'success') {
        if (feedbackTime) feedbackTime.innerHTML = '<div class="alert alert-success">✅ บันทึกประกาศสำเร็จ!</div>';
        formTime.reset();
        updateAnnouncementTable();
        setTimeout(() => { if (feedbackTime) feedbackTime.innerHTML = ''; }, 3000);
      } else {
        if (feedbackTime) feedbackTime.innerHTML = `<div class="alert alert-danger">❌ ไม่สามารถบันทึกได้: ${response.message}</div>`;
      }
    });
  }

  const formTimeAllDay = document.getElementById('announcementFormTimeallDay');
  const feedbackTimeAllDay = document.getElementById('formFeedbackTimeAllDay');
  if (formTimeAllDay) {
    formTimeAllDay.addEventListener('submit', async (e) => {
      e.preventDefault();

      const groupId = document.getElementById('groupId').value;
      const message = document.getElementById('message').value.trim();
      const topic = document.getElementById('topic').value;
      const announcementTime = document.getElementById('announcementTime').value;
      const announcementDate = document.getElementById('announcementDate').value;

      if (!groupId || !topic || !announcementTime || !announcementDate) {
        if (feedbackTimeAllDay) feedbackTimeAllDay.innerHTML = '<div class="alert alert-danger">❌ กรุณากรอกข้อมูลให้ครบถ้วน</div>';
        return;
      }

      const response = await saveAnnouncement(groupId, message, topic, announcementTime, announcementDate);
      if (response.status === 'success') {
        if (feedbackTimeAllDay) feedbackTimeAllDay.innerHTML = '<div class="alert alert-success">✅ บันทึกประกาศสำเร็จ!</div>';
        formTimeAllDay.reset();
        updateAnnouncementTable();
        setTimeout(() => { if (feedbackTimeAllDay) feedbackTimeAllDay.innerHTML = ''; }, 3000);
      } else {
        if (feedbackTimeAllDay) feedbackTimeAllDay.innerHTML = `<div class="alert alert-danger">❌ ไม่สามารถบันทึกได้: ${response.message}</div>`;
      }
    });
  }
});