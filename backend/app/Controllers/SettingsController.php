<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../core/AuditLogger.php';
require_once __DIR__ . '/../Services/SequenceService.php';

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SequenceService::getSettings();
        $sequences = SequenceService::getSequences();

        $this->json([
            'success' => true,
            'settings' => $settings,
            'sequences' => $sequences
        ]);
    }

    public function updateSettings()
    {
        $user = $this->requireAuth();
        if ($user['role'] !== 'ADMIN') {
            $this->json(['error' => 'Admin authorization required.'], 403);
            return;
        }

        $data = $this->getRequestBody();
        $pdo = Model::getDB();

        $allowedSettings = [
            'store_name', 'timezone', 'currency_code', 'currency_symbol',
            'vat_percent', 'vat_calculation_mode', 'price_tax_type', 'date_format',
            'company_address', 'company_phone', 'company_email'
        ];

        // Automatic decimal places rule: BHD, KWD, OMR = 3 decimals; rest = 2 decimals
        $currency = $data['currency_code'] ?? 'BHD';
        $decimalPlaces = in_array($currency, ['BHD', 'KWD', 'OMR']) ? '3' : '2';
        $data['decimal_places'] = $decimalPlaces;
        $allowedSettings[] = 'decimal_places';

        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        foreach ($allowedSettings as $key) {
            if (isset($data[$key])) {
                $stmt->execute([$key, trim($data[$key])]);
            }
        }

        AuditLogger::log($user['user_id'], $user['username'], $user['role'], 'STORE_SETTINGS', 'UPDATE_STORE_SETTINGS', null, $data);

        $this->json([
            'success' => true,
            'message' => 'Store settings updated successfully!',
            'settings' => SequenceService::getSettings()
        ]);
    }

    public function updateSequences()
    {
        $user = $this->requireAuth();
        if ($user['role'] !== 'ADMIN') {
            $this->json(['error' => 'Admin authorization required.'], 403);
            return;
        }

        $data = $this->getRequestBody();
        $sequences = $data['sequences'] ?? [];

        if (empty($sequences) || !is_array($sequences)) {
            $this->json(['error' => 'Sequences payload is required.'], 400);
            return;
        }

        $pdo = Model::getDB();
        $stmt = $pdo->prepare("UPDATE system_sequences SET prefix = ?, padding_length = ?, format_template = ? WHERE sequence_key = ?");

        foreach ($sequences as $seq) {
            if (isset($seq['sequence_key'])) {
                $stmt->execute([
                    trim($seq['prefix'] ?? ''),
                    (int)($seq['padding_length'] ?? 4),
                    trim($seq['format_template'] ?? '{PREFIX}{SEQ}'),
                    $seq['sequence_key']
                ]);
            }
        }

        AuditLogger::log($user['user_id'], $user['username'], $user['role'], 'STORE_SETTINGS', 'UPDATE_SEQUENCE_PREFIXES', null, $sequences);

        $this->json([
            'success' => true,
            'message' => 'Auto-increment prefixes and format templates updated successfully!',
            'sequences' => SequenceService::getSequences()
        ]);
    }

    public function clearTransactionalData()
    {
        $user = $this->requireAuth();
        if ($user['role'] !== 'ADMIN') {
            $this->json(['error' => 'Admin authorization required.'], 403);
            return;
        }

        $pdo = Model::getDB();

        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

            $tables = [
                'branch_payments',
                'credit_note_items',
                'credit_notes',
                'damaged_stock',
                'invoice_payment_records',
                'location_batch_stock',
                'purchase_invoice_items',
                'purchase_invoices',
                'sales_invoice_items',
                'sales_invoices',
                'stock_movements_ledger',
                'stock_return_items',
                'stock_return_rejections',
                'stock_return_wallets',
                'stock_returns',
                'stock_transfer_items',
                'stock_transfers',
                'system_audit_trail',
                'vendor_quotation_items',
                'vendor_quotations',
                'item_batches'
            ];

            foreach ($tables as $table) {
                $pdo->exec("TRUNCATE TABLE `$table`");
            }

            // Reset sequences
            $pdo->exec("UPDATE system_sequences SET current_val = 0");

            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

            AuditLogger::log($user['user_id'], $user['username'], $user['role'], 'SYSTEM_MAINTENANCE', 'CLEAR_TRANSACTIONAL_DATA', null, null);

            $this->json([
                'success' => true,
                'message' => 'All transactional data cleared successfully. Master data has been preserved.'
            ]);
        } catch (\Exception $e) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            $this->error('Failed to clear transactional data: ' . $e->getMessage(), 500);
        }
    }

    public function getLatestProducts()
    {
        $this->requireAuth();
        try {
            $ctx = stream_context_create([
                "http" => [
                    "method" => "GET",
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
                    "timeout" => 5
                ]
            ]);
            $json = @file_get_contents('https://sandslab.com/get_our_latest_products.php', false, $ctx);
            if ($json === false) {
                throw new \Exception("Unable to contact SaNDS Lab server.");
            }
            $data = json_decode($json, true);
            $this->json($data);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
}
