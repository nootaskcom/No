/**
 * Hàm chung gọi đến PHP để lấy link rút gọn cho từng loại nhiệm vụ
 * @param {string} provider - Chọn nhiệm vụ: 'layma' hoặc 'uptolink'
 * @param {string} originalUrl - Link gốc cần rút gọn
 * @param {string} [backupUrl=''] - Link dự phòng (chỉ dùng cho Layma nếu muốn)
 */
async function getTaskLink(provider, originalUrl, backupUrl = '') {
    try {
        const phpEndpoint = 'shorten.php'; // Đường dẫn tới file PHP trên máy chủ
        
        // Tạo chuỗi tham số gửi đi
        const params = new URLSearchParams({
            provider: provider, // Gửi tên nhà cung cấp ('layma' hoặc 'uptolink')
            url: originalUrl,
            link_du_phong: backupUrl
        });

        const response = await fetch(`${phpEndpoint}?${params.toString()}`);
        if (!response.ok) {
            throw new Error(`Lỗi HTTP: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.success) {
            return data.shortenedUrl; // Trả về link đã rút gọn tương ứng
        } else {
            console.error(`Lỗi tạo nhiệm vụ ${provider}:`, data.error);
            alert(`Lỗi: ${data.error}`);
            return null;
        }
    } catch (error) {
        console.error("Lỗi hệ thống:", error);
        return null;
    }
}