<?php
session_start();
require_once 'config.php';

// ディレクトリトラバーサル脆弱性（意図的）
$page = $_GET['page'] ?? 'default';
if ($page === 'default') {
    // pageパラメータがない場合はHTMLを表示
} else {
    // pageパラメータがある場合のみファイル読み込み処理
    $isDirectoryTraversal = (strpos($page, '../') !== false || strpos($page, '..\\') !== false);
    
    // ディレクトリトラバーサル攻撃の場合、暗号化ファイルの復号化を試行
    if ($isDirectoryTraversal) {
        // .encファイルが存在するかチェック
        if (file_exists($page)) {
            try {
                $encryptedContent = file_get_contents($page);
                $decryptedContent = SimpleCrypto::decrypt($encryptedContent);
                
                if ($decryptedContent !== false) {
                    header('Content-Type: text/plain; charset=utf-8');
                    echo "=== 復号化されたファイル内容 ===\n";
                    echo "ファイル: " . htmlspecialchars($page) . "\n";
                    echo "================================\n\n";
                    echo $decryptedContent;
                    exit;
                } else {
                    header('Content-Type: text/plain; charset=utf-8');
                    echo "復号化に失敗しました。";
                    exit;
                }
            } catch (Exception $e) {
                header('Content-Type: text/plain; charset=utf-8');
                echo "エラー: " . $e->getMessage();
                exit;
            }
        }
    }
    
    // 通常のファイルアクセス処理
    $content = processFileRequest($page, $isDirectoryTraversal);
    if ($content !== false) {
        header('Content-Type: text/plain; charset=utf-8');
        echo $content;
        exit;
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "File not found.";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>おすすめの食材と料理</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .food-section {
            margin: 20px 0;
            padding: 15px;
            background: #fafafa;
            border-left: 5px solid #ffc107;
        }
        p {
            line-height: 1.6;
        }
        .stylish-btn {
            padding: 12px 28px;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease-in-out;
            display: inline-block;
        }
        .stylish-btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        .security-hint {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .attack-example {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        code {
            background: #f1f3f4;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="stylish-btn">戻る</a>
        <h1>おすすめの食材と料理</h1>
        
        <div class="food-section">
            <h2>🥔 じゃがいも</h2>
            <p>ポテト<br>✨神✨<br>LOVE_______</p>
        </div>
        
        <div class="food-section">
            <h2>🍲 体が温まる薬膳鍋</h2>
            <p>常に食べていたら体調悪くなりません</p>
        </div>
        
        <div class="food-section">
            <h2>🍛 じゃがいもたっぷりカレー</h2>
            <p>カレーは早く食べることができます</p>
        </div>
        
        
        

        
    </div>
</body>
</html>