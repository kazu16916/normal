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

// ログイン処理
if (isset($_POST['action']) && $_POST['action'] === 'login' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = hash('sha256', $_POST['password']); // SHA256ハッシュ化
    
    // SQLインジェクション脆弱性（意図的）
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $pdo->query($query);
    $user = $result->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] === 'admin') {
            header('Location: admin.php');
            exit;
        } else {
            header('Location: dashboard.php');
            exit;
        }
    } else {
        $error = "ログインに失敗しました";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>セキュリティ演習サイト - ログイン</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            background: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            margin-top: 10px;
            text-align: center;
        }
        .help-link {
            text-align: center;
            margin-top: 20px;
        }
        .help-link a {
            color: #007bff;
            text-decoration: none;
        }
        .test-credentials {
            background: #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        h4{
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>セキュリティ演習サイト</h1>
        
        <div class="test-credentials">
            <strong>テストユーザ:</strong><br>
            ユーザー名: test<br>
            パスワード: test123
        </div>
        <h4>どんなユーザ名へのログイン試行でも<br>３回失敗するとロックされます。と仮定</h4>
        
        <form method="POST">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label for="username">ユーザー名:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">パスワード:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">ログイン</button>
        </form>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>