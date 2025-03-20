const CONFIG = {
    API_BASE_URL: 'datascss/',
    FETCH_DELAY: 1000,
    ENDPOINTS: {
        GET_USERS: 'get-users.php',
        DELETE_USER: 'delete-user.php' // เพิ่ม endpoint สำหรับลบผู้ใช้
    },

    // ฟังก์ชันสำหรับดึงและแสดงข้อมูลผู้ใช้
    async fetchAndDisplayUsers(tbodyId = 'tableUser') {
        try {
            const tbody = document.getElementById(tbodyId);
            if (!tbody) {
                throw new Error(`ไม่พบ tbody ที่มี id="${tbodyId}" ในหน้าเว็บ`);
            }

            const url = `${this.API_BASE_URL}${this.ENDPOINTS.GET_USERS}`;
            await new Promise(resolve => setTimeout(resolve, this.FETCH_DELAY));
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const users = await response.json();

            // แสดงข้อมูลใน tbody พร้อมปุ่ม Delete
            tbody.innerHTML = users.map(user => 
                `<tr>
                    <td>${user.username}</td>
                    <td>${user.role}</td>
                    <td><button class="delete-btn btn btn-sm btn-danger" data-username="${user.username}">Delete</button></td>
                </tr>`
            ).join('');

            // เพิ่ม event listener ให้ปุ่ม Delete
            this.addDeleteListeners(tbodyId);
        } catch (error) {
            console.error('Error:', error);
            const tbody = document.getElementById(tbodyId);
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="3">เกิดข้อผิดพลาด: ${error.message}</td></tr>`;
            }
        }
    },

    // ฟังก์ชันสำหรับเพิ่ม event listener ให้ปุ่ม Delete
    addDeleteListeners(tbodyId) {
        const deleteButtons = document.querySelectorAll(`#${tbodyId} .delete-btn`);
        deleteButtons.forEach(button => {
            button.addEventListener('click', async (e) => {
                const username = e.target.getAttribute('data-username');
                if (confirm(`คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้ ${username}?`)) {
                    await this.deleteUser(username, tbodyId);
                }
            });
        });
    },

    // ฟังก์ชันสำหรับลบผู้ใช้
    async deleteUser(username, tbodyId) {
        try {
            const url = `${this.API_BASE_URL}${this.ENDPOINTS.DELETE_USER}`;
            const response = await fetch(url, {
                method: 'POST', // หรือ 'DELETE' ขึ้นอยู่กับ server
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username: username })
            });

            if (!response.ok) {
                throw new Error(`ไม่สามารถลบผู้ใช้ได้! status: ${response.status}`);
            }

            // รีเฟรชตารางหลังลบสำเร็จ
            await this.fetchAndDisplayUsers(tbodyId);
        } catch (error) {
            console.error('Delete error:', error);
            alert(`เกิดข้อผิดพลาดในการลบ: ${error.message}`);
        }
    },

    // ฟังก์ชันเริ่มต้น
    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.fetchAndDisplayUsers());
        } else {
            this.fetchAndDisplayUsers();
        }
    }
};

// เรียก init อัตโนมัติ
CONFIG.init();