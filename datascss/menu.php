<?php
require_once '../auth.php';
requireLogin(); // ต้องล็อกอินก่อน
?>

<ul class="menu-inner py-1">
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { ?>
        <!-- เมนูสำหรับ Admin (เห็นทั้งหมด) -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Send Message</span>
        </li>
        <li class="menu-item">
            <a href="index.php" class="menu-link">
                <img src="../assets/img/bot/44.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Dashboards">Message Onepay</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="onebank.php" class="menu-link">
                <img src="../assets/img/bot/33.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Dashboards">Message Onebank</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="messagelog.php" class="menu-link">
                <img src="../assets/img/bot/22.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Dashboards">Log Message</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Group Chat ID</span>
        </li>
        <li class="menu-item">
            <a href="group.php" class="menu-link">
                <img src="../assets/img/bot/55.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Email">Group ID</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="addgroup.php" class="menu-link">
                <img src="../assets/img/bot/99.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Email">Add Group ChatID</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Add/Edit Text Message</span>
        </li>
        <li class="menu-item">
            <a href="message.php" class="menu-link">
                <img src="../assets/img/bot/13.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Basic">Edit Text All</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="addmessage.php" class="menu-link">
                <img src="../assets/img/bot/12.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Basic">Add Announcement Message</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="addmessagetime.php" class="menu-link">
                <img src="../assets/img/bot/15.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Basic">Add Message AutoTime</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="addmessagedayalltime.php" class="menu-link">
                <img src="../assets/img/bot/77.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Basic">Add Message AutoDayallTime</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Telegram Token</span>
        </li>
        <li class="menu-item">
            <a href="token.php" class="menu-link">
                <img src="../assets/img/bot/16.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Basic">Token</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="addtoken.php" class="menu-link">
                <img src="../assets/img/bot/17.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Basic">Add Token</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Bot Settings</span>
        </li>
        <li class="menu-item">
            <a href="botsettings.php" class="menu-link">
                <img src="../assets/img/bot/66.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Basic">Settings</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Account</span>
        </li>
        <li class="menu-item">
            <a href="register.php" class="menu-link">
                <img src="../assets/img/bot/14.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Email">Add Customer</div>
            </a>
        </li>

        <li class="menu-item">
            <a href="logout.php" class="menu-link">
                <img src="../assets/img/bot/88.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Email">Logout</div>
            </a>
        </li>

    <?php } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'user') { ?>
        <!-- เมนูสำหรับ User (กำหนดเอง) -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Send Message</span>
        </li>
        <li class="menu-item">
            <a href="index.php" class="menu-link">
                <img src="../assets/img/bot/44.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Dashboards">Message Onepay</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="onebank.php" class="menu-link">
                <img src="../assets/img/bot/33.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Dashboards">Message Onebank</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="messagelog.php" class="menu-link">
                <img src="../assets/img/bot/22.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Dashboards">Log Message</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Add/Edit Text Message</span>
        </li>
        <li class="menu-item">
            <a href="message.php" class="menu-link">
                <img src="../assets/img/bot/13.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Basic">Edit Text All</div>
            </a>
        </li>

        <li class="menu-item">
            <a href="logout.php" class="menu-link">
                <img src="../assets/img/bot/88.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Email">Logout</div>
            </a>
        </li>

    <?php } else { ?>
        <!-- ถ้าไม่มี role หรือไม่ได้ล็อกอิน (ไม่น่าถึงจุดนี้เพราะ requireLogin) -->
        <li class="menu-item">
            <a href="login.php" class="menu-link">
                <img src="../assets/img/bot/88.png" class="menu-icon" />
                <div class="text-truncate" data-i18n="Email">Login</div>
            </a>
        </li>
    <?php } ?>
</ul>