const CONFIG = {
    API_BASE_URL: 'datascss/',
    FETCH_DELAY: 1000,
    ENDPOINTS: {
        GET_GROUPS: 'get-groups.php',
    }
};

const fixedGroup = "GroupA";

        function sendMessage() {
            const message = document.getElementById("messageInput").value;
            if (message.trim() === "") {
                alert("กรุณาพิมพ์ข้อความก่อนส่ง");
                return;
            }
            const status = document.getElementById("status");
            status.innerHTML = `กำลังส่ง: "${message}" ไปยังกลุ่ม ${fixedGroup}...`;
            document.getElementById("messageInput").value = "";
            setTimeout(() => {
                status.innerHTML = "ส่งข้อความสำเร็จ!";
            }, 1000);
        }

        document.getElementById("messageInput").addEventListener("keydown", function(event) {
            if (event.ctrlKey && event.key === "Enter") {
                sendMessage();
            }
        });