<?php
// データベース設定
$host = 'db';
$dbname = 'security_exercise';
$username = 'root';
$password = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("データベース接続エラー: " . $e->getMessage());
}

// 暗号化/復号化機能
class SimpleCrypto {
    private static $key = 'security_exercise_key_2024';
    
    public static function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', self::$key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    public static function decrypt($data) {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', self::$key, 0, $iv);
    }
}

// ディレクトリトラバーサル攻撃検出と復号化機能
function processFileRequest($filename, $isDirectoryTraversal = false) {
    // ディレクトリトラバーサル攻撃が検出された場合のみ復号化
    if ($isDirectoryTraversal && (strpos($filename, '../') !== false || strpos($filename, '..\\') !== false)) {
        if (file_exists($filename . '.enc')) {
            $encryptedContent = file_get_contents($filename . '.enc');
            return SimpleCrypto::decrypt($encryptedContent);
        }
    }
    
    // 通常のファイルアクセス
    if (file_exists($filename)) {
        return file_get_contents($filename);
    }
    
    return false;
}
?>