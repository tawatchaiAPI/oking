<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['website'])) {
    $url = filter_var($_POST['website'], FILTER_SANITIZE_URL);
    
    // แยกชื่อโดเมนจาก URL
    $parsedUrl = parse_url($url);
    $domain = $parsedUrl['host'] ?? $url;  // ถ้าไม่มี host ให้ใช้ URL แทน
    
    // ฟังก์ชันตรวจสอบ DNS
    function checkDNSBlocked($domain) {
        $dnsResult = dns_get_record($domain, DNS_A); // ดึง DNS record ประเภท A
        if (empty($dnsResult)) {
            return "🚫 เว็บไซต์ถูกบล็อกในระดับ DNS (ไม่สามารถแปลงโดเมนเป็น IP ได้)";
        }
        return "✅ DNS ปกติ";
    }

    //ตรวจสอบ HTTP บล็อก (ใช้ cURL)
    function checkHTTPBlocked($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // ติดตามการ Redirect
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
    
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // ตรวจสอบ HTTP Code
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // ตรวจสอบ URL หลังจาก Redirect
        curl_close($ch);
    
        // ตรวจสอบ Response Code และการ Redirect
        if ($httpCode == 403 || $httpCode == 451) {
            return "🚫 เว็บไซต์ถูกบล็อกโดย HTTP (HTTP Code: $httpCode)";
        }
        
        if (strpos($finalUrl, "illegal.mdes.go.th") !== false) {
            return "🚫 เว็บไซต์ถูกบล็อกโดย ICT (Redirected to MDES)";
        }
    
        if ($httpCode >= 400) {
            return "⚠️ เว็บไซต์ไม่สามารถเข้าถึงได้ (HTTP Code: $httpCode)";
        }
    
        return "✅ เว็บไซต์สามารถเข้าถึงได้";
    }
    
    // ฟังก์ชันตรวจสอบการเชื่อมต่อ TCP
    function checkTCPConnection($domain, $port = 80) {
        $conn = @fsockopen($domain, $port, $errno, $errstr, 5);
        if (!$conn) {
            return "🚫 ไม่สามารถเชื่อมต่อไปยัง $domain (Port: $port)";
        }
        fclose($conn);
        return "✅ Port $port ของ $domain เปิดอยู่";
    }

    // ฟังก์ชันใช้ cURL เพื่อตรวจสอบการเข้าถึงเว็บไซต์
    function fetchWebsiteContent($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        // ตรวจสอบ response header
        curl_setopt($ch, CURLOPT_HEADER, true); // เปิดให้ cURL ดึง header ด้วย
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $responseHeader = curl_getinfo($ch, CURLINFO_HEADER_OUT); // หารายละเอียด header ที่ได้รับ
        curl_close($ch);

        return ['content' => $output, 'http_code' => $httpCode, 'final_url' => $finalUrl, 'header' => $responseHeader];
    }

    // ฟังก์ชันตรวจสอบว่าเว็บไซต์ถูกบล็อก
    function checkWebsiteBlocked($url, $domain) {
        // ตรวจสอบ DNS Blocked
        $dnsStatus = checkDNSBlocked($domain);
        
        // ตรวจสอบ HTTP Blocked
        $httpStatus = checkHTTPBlocked($url);
    
        // รวมผลลัพธ์ทั้ง DNS และ HTTP
        return "<h3>ผลการตรวจสอบ</h3><p>$dnsStatus</p><p>$httpStatus</p>";
    }
    
    echo checkWebsiteBlocked($url, $domain);

    function fetchWebsiteWithProxy($url, $proxy) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_PROXY, $proxy); // ตั้งค่า Proxy
        curl_setopt($ch, CURLOPT_PROXYPORT, 8080); // ตั้งค่า Port ของ Proxy (ถ้าจำเป็น)
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // ตรวจสอบ HTTP Code
        curl_close($ch);
        
        return ['content' => $output, 'http_code' => $httpCode];
    }
    

    $result = fetchWebsiteWithProxy($url, $proxy);
    
    echo "HTTP Code: " . $result['http_code'];
    if ($result['http_code'] == 403 || $result['http_code'] == 451) {
        echo "เว็บไซต์ถูกบล็อก";
    } else {
        echo "เว็บไซต์สามารถเข้าถึงได้";
    }
    
    
}
?>

