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

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// ユーザ検索機能（ブラインドSQLインジェクション脆弱性）
$searchResult = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    
    try {
        // 通常の検索では一般ユーザのみ検索可能（adminは除外）
        // ただし、SQLインジェクションを使えばすべてのユーザ情報にアクセス可能
        $query = "SELECT username FROM users WHERE username LIKE '%$search%' AND role != 'admin' LIMIT 1";
        $result = $pdo->query($query);
        
        if ($result && $result->rowCount() > 0) {
            // ブラインドSQLインジェクション演習のため、具体的なユーザ名は表示しない
            $searchResult = "検索条件に一致するユーザが存在します";
        } else {
            $searchResult = "検索条件に一致するユーザは見つかりません";
        }
    } catch (PDOException $e) {
        // SQLエラーが発生した場合（SQLインジェクション成功の場合など）
        $searchResult = "検索処理でエラーが発生しました";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ダッシュボード</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
        }
        .search-form {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        input[type="text"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
            width: 200px;
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
        .logout-btn {
            background: #dc3545;
        }
        .logout-btn:hover {
            background: #c82333;
        }
        .result {
            margin-top: 10px;
            padding: 10px;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
        }
        .hint {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .stylish-btn {
            /* ボタン内部の余白 */
            padding: 12px 28px;
            
            /* 文字の色 */
            color: white;
            
            /* 背景色 */
            background-color: #007bff;
            
            /* 枠線 */
            border: none;
            
            /* 角の丸み */
            border-radius: 8px;
            
            /* リンクの下線を消す */
            text-decoration: none;
            
            /* フォントの太さ */
            font-weight: bold;
            
            /* 立体感を出す影 */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            
            /* カーソルを合わせた時のアニメーション速度 */
            transition: all 0.2s ease-in-out;
            
            /* ブロック要素として振る舞わせる */
            display: inline-block;
        }

        /* カーソルを合わせた時のスタイル */
        .stylish-btn:hover {
            /* 少し濃い色に変化 */
            background-color: #0056b3;
            
            /* 少し上に浮き上がる効果 */
            transform: translateY(-2px);
            
            /* 影を少し強調 */
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="cook.php" class="stylish-btn">おすすめ料理</a>
        <div class="header">
            <h1>ダッシュボード</h1>
            <div>
                <span>ようこそ、<?php echo htmlspecialchars($_SESSION['username']); ?>さん (<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
                <a href="logout.php"><button class="logout-btn">ログアウト</button></a>
            </div>
        </div>
        
        <h2>ユーザ検索</h2>
        <div class="search-form">
            <form method="GET">
                <input type="text" name="search" placeholder="ユーザ名を入力" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit">検索</button>
            </form>
            <?php if ($searchResult): ?>
                <div class="result"><?php echo $searchResult; ?></div>
            <?php endif; ?>
        </div>
        
        
    </div>
</body>
</html>