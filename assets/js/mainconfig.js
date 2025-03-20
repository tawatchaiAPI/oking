const CONFIG = {
  API_BASE_URL: 'datascss/', // URL ของ API
  FETCH_DELAY: 1000, // หน่วงเวลาในการดึงข้อมูล (1 วินาที)
  ENDPOINTS: {
    GET_GROUPS: 'get-groups.php',
    DELETE_GROUP: 'delete-group.php',
    ADD_GROUP: 'add-group.php',
    EDIT_GROUP: 'edit_group.php',
  }
};

// 📌 ฟังก์ชันดึงข้อมูลกลุ่ม
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

// 📌 ฟังก์ชันเพิ่มกลุ่ม
export async function addGroup(groupName, chatIds) {
  if (!groupName || !chatIds) {
    return { status: 'error', message: 'กรุณากรอกชื่อกลุ่มและรหัสแชทให้ครบถ้วน' };
  }

  try {
    const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.ADD_GROUP, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json; charset=UTF-8' },
      body: JSON.stringify({ group_name: groupName, chat_ids: chatIds })
    });

    if (!response.ok) throw new Error(`ไม่สามารถเพิ่มกลุ่มได้: ${response.status}`);
    const result = await response.json();
    console.log("✅ เพิ่มกลุ่มสำเร็จ:", result);
    return result;
  } catch (error) {
    console.error("❌ ข้อผิดพลาดในการเพิ่มกลุ่ม:", error);
    return { status: 'error', message: error.message };
  }
}

// 📌 ฟังก์ชันลบกลุ่ม
export async function deleteGroup(groupId) {
  if (!groupId) {
    console.error("❌ รหัสกลุ่มไม่ถูกต้อง");
    return { status: 'error', message: 'รหัสกลุ่มไม่ถูกต้อง' };
  }

  try {
    const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.DELETE_GROUP, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json; charset=UTF-8' },
      body: JSON.stringify({ group_id: groupId })
    });

    const textResponse = await response.text();
    console.log("คำตอบดิบจาก delete-group.php:", textResponse); // Debug response ดิบ

    if (!response.ok) {
      throw new Error(`ไม่สามารถลบกลุ่มได้: ${response.status} ${response.statusText}`);
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
    console.error("❌ ข้อผิดพลาดในการลบกลุ่ม:", error);
    return { status: 'error', message: error.message };
  }
}

// 📌 ฟังก์ชันแก้ไขกลุ่ม
export async function editGroup(id, groupName, chatIds) {
  if (!id || !groupName || !chatIds) {
    return { status: 'error', message: 'กรุณากรอกข้อมูลให้ครบถ้วน' };
  }

  try {
    const response = await fetch(CONFIG.API_BASE_URL + CONFIG.ENDPOINTS.EDIT_GROUP, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json; charset=UTF-8' },
      body: JSON.stringify({ id, group_name: groupName, chat_ids: chatIds })
    });

    if (!response.ok) throw new Error(`ไม่สามารถแก้ไขกลุ่มได้: ${response.status}`);
    const result = await response.json();
    console.log("✅ แก้ไขกลุ่มสำเร็จ:", result);
    return result;
  } catch (error) {
    console.error("❌ ข้อผิดพลาดในการแก้ไขกลุ่ม:", error);
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
    if (data.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="4" class="text-center">ไม่พบข้อมูลกลุ่ม</td></tr>';
      return;
    }

    data.forEach(row => {
      const chatIds = row.chat_ids ? row.chat_ids.split(",").map(id => id.trim()).join(", ") : "-";
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${row.group_id}</td>
        <td>${row.group_name}</td>
        <td>${chatIds}</td>
        <td>
          <button class="btn btn-sm btn-primary edit" data-id="${row.group_id}" data-name="${row.group_name}" data-chat-ids="${row.chat_ids}">แก้ไข</button>
          <button class="btn btn-sm btn-danger delete" data-id="${row.group_id}">ลบ</button> 
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
      const groupId = event.target.getAttribute("data-id");
      console.log("เริ่มลบกลุ่ม ID:", groupId); // Debug

      if (confirm("คุณแน่ใจว่าต้องการลบกลุ่มนี้หรือไม่?")) {
        const response = await deleteGroup(groupId);
        console.log("ผลลัพธ์การลบ:", response); // Debug ผลลัพธ์

        if (response && response.status === 'success') {
          alert("✅ ลบกลุ่มสำเร็จ!");
          fetchGroupsForTable(tableId);
        } else {
          const errorMessage = response && response.message ? response.message : 'เกิดข้อผิดพลาดไม่ทราบสาเหตุ';
          alert(`❌ ไม่สามารถลบกลุ่มได้: ${errorMessage}`);
        }
      } else {
        console.log("ยกเลิกการลบ");
      }
    }

    // กรณีคลิกปุ่ม "แก้ไข"
    if (event.target.classList.contains("edit")) {
      const groupId = event.target.getAttribute("data-id");
      const currentName = event.target.getAttribute("data-name");
      const currentChatIds = event.target.getAttribute("data-chat-ids");

      // เติมข้อมูลลงใน modal
      document.getElementById("editGroupId").value = groupId;
      document.getElementById("editGroupName").value = currentName;
      document.getElementById("editChatIds").value = currentChatIds;

      // แสดง modal
      const modal = new bootstrap.Modal(document.getElementById("editGroupModal"));
      modal.show();
    }
  });

  // จัดการการบันทึกจาก modal
  const saveButton = document.getElementById("saveEditGroup");
  if (saveButton) {
    saveButton.addEventListener("click", async () => {
      const groupId = document.getElementById("editGroupId").value;
      const groupName = document.getElementById("editGroupName").value.trim();
      const chatIds = document.getElementById("editChatIds").value.trim();

      if (!groupName || !chatIds) {
        alert("❌ กรุณากรอกชื่อกลุ่มและรหัสแชทให้ครบถ้วน");
        return;
      }

      const response = await editGroup(groupId, groupName, chatIds);
      if (response.status === 'success') {
        alert("✅ แก้ไขกลุ่มสำเร็จ!");
        fetchGroupsForTable(tableId);
        bootstrap.Modal.getInstance(document.getElementById("editGroupModal")).hide();
      } else {
        alert(`❌ ไม่สามารถแก้ไขกลุ่มได้: ${response.message || 'เกิดข้อผิดพลาดไม่ทราบสาเหตุ'}`);
      }
    });
  }
}

// 📌 ฟังก์ชันดึงข้อมูลและแสดงผลในตาราง
async function fetchGroupsForTable(tableId) {
  try {
    const data = await fetchGroups();
    updateTable(data, tableId);
  } catch (error) {
    console.error(`❌ ข้อผิดพลาดในการดึงข้อมูลสำหรับ ${tableId}:`, error);
  }
}

// 📌 เมื่อโหลดหน้าเว็บ
document.addEventListener("DOMContentLoaded", () => {
  const tableId = "groupTableBody";
  fetchGroupsForTable(tableId);
  setupTableEventListeners(tableId);

  const form = document.getElementById('addGroupForm');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const groupName = document.getElementById('newGroupName').value.trim();
      const chatIds = document.getElementById('newChatIds').value.trim();

      if (!groupName || !chatIds) {
        alert("❌ กรุณากรอกชื่อกลุ่มและรหัสแชทให้ครบถ้วน");
        return;
      }

      const response = await addGroup(groupName, chatIds);
      if (response.status === 'success') {
        alert('✅ เพิ่มกลุ่มสำเร็จ!');
        form.reset();
        fetchGroupsForTable(tableId);
      } else {
        alert(`❌ ไม่สามารถเพิ่มกลุ่มได้: ${response.message || 'เกิดข้อผิดพลาดไม่ทราบสาเหตุ'}`);
      }
    });
  } else {
    console.error("❌ ไม่พบฟอร์ม 'addGroupForm'");
  }
});

export default CONFIG;