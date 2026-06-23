<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// CẤU HÌNH API KEY CỦA 2 BÊN
define('LAYMA_API_TOKEN', 'cbd6acea472870f7e828aca17c66d613');
define('LAYMA_API_URL', 'https://api.layma.net/api/admin/shortlink/quicklink');

define('UPTOLINK_API_TOKEN', '38c99831f60c5e412249e59854395ed181af7a69');
define('UPTOLINK_API_URL', 'https://uptolink.vip/api');

// Hàm gọi API trung gian bằng cURL
function send_request($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
    $response = curl_exec($ch);
    curl_close($ch);
    if ($response === false) {
        $response = @file_get_contents($url);
    }
    return $response;
}

// Lấy tham số từ client gửi lên
$destUrl = isset($_REQUEST['url']) ? trim($_REQUEST['url']) : '';
$provider = isset($_REQUEST['provider']) ? trim(strtolower($_REQUEST['provider'])) : ''; // 'layma' hoặc 'uptolink'
$linkDuPhong = isset($_REQUEST['link_du_phong']) ? trim($_REQUEST['link_du_phong']) : '';
$type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 4; // Mặc định loại quảng cáo Uptolink là 4
// API Token gửi lên từ từng nhiệm vụ (cấu hình ở trang Admin). Nếu trống sẽ dùng token mặc định bên dưới.
$apiTokenFromRequest = isset($_REQUEST['api_token']) ? trim($_REQUEST['api_token']) : '';

// Kiểm tra tính hợp lệ của link đích
if (empty($destUrl) || !filter_var($destUrl, FILTER_VALIDATE_URL)) {
    echo json_encode([
        'success' => false,
        'error' => 'Đường dẫn đích (url) không hợp lệ hoặc bị thiếu.'
    ]);
    exit;
}

// XỬ LÝ RIÊNG BIỆT CHO TỪNG NHIỆM VỤ
if ($provider === 'layma') {
    // Ưu tiên token gửi lên từ nhiệm vụ (admin nhập trực tiếp ở giao diện); nếu không có thì dùng token mặc định
    $laymaToken = $apiTokenFromRequest !== '' ? $apiTokenFromRequest : LAYMA_API_TOKEN;
    // Nếu là nhiệm vụ Layma
    $apiUrl = LAYMA_API_URL . '?' . http_build_query([
        'tokenUser' => $laymaToken,
        'url' => $destUrl,
        'link_du_phong' => $linkDuPhong,
        'format' => 'text'
    ]);
    $response = send_request($apiUrl);
    $shortUrl = trim($response);

    if (filter_var($shortUrl, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => true, 'shortenedUrl' => $shortUrl]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Layma.net phản hồi lỗi hoặc trả về rỗng.']);
    }

} elseif ($provider === 'uptolink') {
    // Ưu tiên token gửi lên từ nhiệm vụ (admin nhập trực tiếp ở giao diện); nếu không có thì dùng token mặc định
    $uptolinkToken = $apiTokenFromRequest !== '' ? $apiTokenFromRequest : UPTOLINK_API_TOKEN;
    // Nếu là nhiệm vụ Uptolink
    $apiUrl = UPTOLINK_API_URL . '?' . http_build_query([
        'api' => $uptolinkToken,
        'url' => $destUrl,
        'type' => $type,
        'format' => 'text'
    ]);
    $response = send_request($apiUrl);
    $shortUrl = trim($response);

    if (filter_var($shortUrl, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => true, 'shortenedUrl' => $shortUrl]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Uptolink.vip phản hồi lỗi hoặc trả về rỗng.']);
    }

} else {
    // Nếu không truyền đúng tên nhà cung cấp nhiệm vụ
    echo json_encode([
        'success' => false,
        'error' => 'Yêu cầu không hợp lệ. Vui lòng chọn provider là layma hoặc uptolink.'
    ]);
}
exit;