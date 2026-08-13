<?php
define('APP_ROOT', __DIR__ . '/..');
require_once APP_ROOT . '/core/Model.php';

echo "========================================================\n";
echo "    CLEAR TRANSACTIONAL DATA SCRIPT (PRESERVE MASTERS)   \n";
echo "========================================================\n\n";

try {
    $pdo = Model::getDB();
    
    // Disable Foreign Key Constraints
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "[1/4] Foreign key checks disabled.\n";

    // 21 Transactional Tables to Truncate
    $transactionalTables = [
        'purchase_invoice_items',
        'purchase_invoices',
        'vendor_quotation_items',
        'vendor_quotations',
        'stock_transfer_items',
        'stock_transfers',
        'sales_invoice_items',
        'sales_invoices',
        'stock_return_items',
        'stock_returns',
        'stock_return_rejections',
        'stock_return_wallets',
        'credit_note_items',
        'credit_notes',
        'damaged_stock',
        'location_batch_stock',
        'item_batches',
        'stock_movements_ledger',
        'invoice_payment_records',
        'branch_payments',
        'system_audit_trail'
    ];

    echo "[2/4] Truncating 21 transactional tables...\n";
    foreach ($transactionalTables as $table) {
        $pdo->exec("TRUNCATE TABLE `$table`");
        echo "  - Truncated table: `$table`\n";
    }

    // Reset Auto-Increment Sequences in system_sequences
    echo "[3/4] Resetting auto-increment sequence counters...\n";
    $pdo->exec("UPDATE `system_sequences` SET `current_val` = 0");
    echo "  - Reset all sequence counters in `system_sequences` to current_val = 0\n";

    // Re-enable Foreign Key Constraints
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "[4/4] Foreign key checks re-enabled.\n\n";

    echo "SUCCESS: Database transactional data cleared successfully!\n";
    echo "All master data (Items, Categories, Locations, Vendors, Customers, Doctors, Users, Settings) preserved intact.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
