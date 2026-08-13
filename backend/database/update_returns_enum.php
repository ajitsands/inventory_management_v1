<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/core/Model.php';

try {
    $pdo = Model::getDB();

    echo "=== UPDATING RETURN STATUS ENUM DEFINITIONS ===\n";

    // 1. Alter stock_returns status enum
    $pdo->exec("ALTER TABLE `stock_returns` MODIFY COLUMN `status` ENUM('PENDING_ACCEPTANCE','ACCEPTED','REJECTED','PARTIALLY_ACCEPTED','RESTORED','RESTORED_TO_STOCK') NOT NULL DEFAULT 'PENDING_ACCEPTANCE'");
    echo "[1/4] Altered `stock_returns` status column ENUM to include RESTORED.\n";

    // 2. Alter stock_return_items status enum
    $pdo->exec("ALTER TABLE `stock_return_items` MODIFY COLUMN `status` ENUM('PENDING','ACCEPTED','REJECTED','RESTORED','RESTORED_TO_STOCK') NOT NULL DEFAULT 'PENDING'");
    echo "[2/4] Altered `stock_return_items` status column ENUM to include RESTORED.\n";

    // 3. Alter stock_return_rejections status enum
    $pdo->exec("ALTER TABLE `stock_return_rejections` MODIFY COLUMN `status` ENUM('IN_REJECT_WALLET','RESTORED','RESTORED_TO_STOCK') NOT NULL DEFAULT 'IN_REJECT_WALLET'");
    echo "[3/4] Altered `stock_return_rejections` status column ENUM to include RESTORED.\n";

    // 4. Update any existing blank status records to RESTORED
    $upd1 = $pdo->exec("UPDATE `stock_returns` SET `status` = 'RESTORED' WHERE `status` = '' OR `status` IS NULL");
    $upd2 = $pdo->exec("UPDATE `stock_return_items` SET `status` = 'RESTORED' WHERE `status` = '' OR `status` IS NULL");
    $upd3 = $pdo->exec("UPDATE `stock_return_rejections` SET `status` = 'RESTORED' WHERE `status` = '' OR `status` IS NULL OR `status` = 'RESTORED_TO_STOCK'");
    echo "[4/4] Cleaned up existing empty status records -> Updated to 'RESTORED'.\n";

    echo "\nSUCCESS: Database status ENUM updated successfully!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
