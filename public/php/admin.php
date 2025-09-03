<?php
// 1. 復号化キーを持つconfig.phpを最初に読み込む
require_once 'config.php';

// 2. 暗号化されたDB初期化スクリプトを読み込む
$encryptedCode = file_get_contents('../sql/init-db.php.enc');

// 3. 読み込んだ内容を復号化する
$decryptedCode = SimpleCrypto::decrypt($encryptedCode);

// 4. 復号化された内容からPHP開始タグを除去してからeval()で実行
if ($decryptedCode) {
    // PHP開始タグ <?php を除去
    $cleanCode = preg_replace('/^<\?php\s*/', '', $decryptedCode);
    eval($cleanCode);
}
session_start();
require_once 'config.php';

// 管理者権限チェック
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$encryptResult = '';
$encryptError = '';

// 暗号化機能（危険：パストラバーサル可能）
if (isset($_POST['action']) && $_POST['action'] === 'encrypt' && isset($_POST['path'])) {
    $path = $_POST['path'];
    
    try {
        // 危険：任意のパスを許可
        if ($path === './') {
            // すべてのファイルを暗号化する危険な処理
            $files = glob('./*');
            $encryptedFiles = [];
            
            foreach ($files as $file) {
                if (is_file($file) && !str_ends_with($file, '.enc')) {
                    $content = file_get_contents($file);
                    $encrypted = SimpleCrypto::encrypt($content);
                    $encFile = $file;
                    file_put_contents($encFile, $encrypted);
                    $encryptedFiles[] = $file;
                }
            }
            
            $encryptResult = "暗号化完了: " . implode(', ', $encryptedFiles);
        } else {
            if (file_exists($path) && is_file($path)) {
                $content = file_get_contents($path);
                $encrypted = SimpleCrypto::encrypt($content);
                $encFile = $path;
                file_put_contents($encFile, $encrypted);
                $encryptResult = "ファイル '{$path}' を暗号化しました";
            } else {
                $encryptError = "ファイルが見つかりません: {$path}";
            }
        }
    } catch (Exception $e) {
        $encryptError = "暗号化エラー: " . $e->getMessage();
    }
}

// 復号化機能
if (isset($_POST['action']) && $_POST['action'] === 'decrypt' && isset($_POST['file'])) {
    $file = $_POST['file'];
    
    try {
        if (file_exists($file) && str_ends_with($file, '.enc')) {
            $encryptedContent = file_get_contents($file);
            $decrypted = SimpleCrypto::decrypt($encryptedContent);
            
            // 復号化内容を表示
            header('Content-Type: text/plain');
            echo $decrypted;
            exit;
        } else {
            $encryptError = "暗号化ファイルが見つかりません: {$file}";
        }
    } catch (Exception $e) {
        $encryptError = "復号化エラー: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者パネル</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: #dc3545;
            color: white;
            padding: 15px;
            border-radius: 4px;
        }
        .admin-section {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #dc3545;
        }
        input[type="text"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
            width: 300px;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .encrypt-btn {
            background: #dc3545;
        }
        .encrypt-btn:hover {
            background: #c82333;
        }
        .logout-btn {
            background: #6c757d;
        }
        .logout-btn:hover {
            background: #5a6268;
        }
        .result, .error {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
        }
        .result {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .dangerous {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 管理者パネル</h1>
            <div>
                <span>管理者: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php"><button class="logout-btn">ログアウト</button></a>
            </div>
        </div>
        
        <div class="dangerous">
            <strong>⚠️ 危険なシステム警告 ⚠️</strong><br>
            このシステムは任意のパスのファイルを暗号化できます。<br>
        </div>
        
        <div class="admin-section">
            <h2>🔐 ファイル暗号化システム</h2>
            <p>管理者のみがアクセスできる暗号化システムです。</p>
            
            <form method="POST">
                <input type="hidden" name="action" value="encrypt">
                <div class="form-group">
                    <label for="path">暗号化するファイル/ディレクトリのパス:</label>
                    <input type="text" id="path" name="path" required>
                    <button type="submit" class="encrypt-btn">暗号化実行</button>
                </div>
            </form>
            
            <?php if ($encryptResult): ?>
                <div class="result"><?php echo htmlspecialchars($encryptResult); ?></div>
            <?php endif; ?>
            
            <?php if ($encryptError): ?>
                <div class="error"><?php echo htmlspecialchars($encryptError); ?></div>
            <?php endif; ?>
        </div>
        
        <div class="admin-section">
            <h2>🔓 ファイル復号化システム</h2>
            <p>暗号化されたファイルを復号化して内容を確認できます。</p>
            
            <form method="POST">
                <input type="hidden" name="action" value="decrypt">
                <div class="form-group">
                    <label for="file">復号化するファイル(.encファイル):</label>
                    <input type="text" id="file" name="file" placeholder="例: init.sql.enc" required>
                    <button type="submit">復号化して表示</button>
                </div>
            </form>
        </div>
        
        
    </div>
</body>
</html>