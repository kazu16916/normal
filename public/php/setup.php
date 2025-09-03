<?php
require_once 'config.php';

// 変更点: ../sql/init-db.php を正しく指すようにパスを修正
$initDbContent = file_get_contents('../sql/init-db.php');

// 暗号化して ../sql/init-db.php.enc として保存
$encrypted = SimpleCrypto::encrypt($initDbContent);
file_put_contents('../sql/init-db.php.enc', $encrypted);

echo "init-db.php.enc ファイルを作成しました。\n";
?>