<?php
require_once __DIR__ . '/../../core/Controller.php';

class ReportController extends Controller {

    public function getMovementLedger() {
        $user = $this->requireAuth();
        $pdo = Model::getDB();

        $userLocId = $user['location_id'] ?? null;
        $isGlobalAdmin = ($user['role'] === 'ADMIN') || empty($userLocId);

        if ($isGlobalAdmin) {
            $sql = "SELECT sml.*, i.name AS item_name, i.item_code, b.batch_code, b.expiry_date,
                           fl.name AS from_location_name, tl.name AS to_location_name, u.full_name AS created_by_name
                    FROM `stock_movements_ledger` sml
                    JOIN `items` i ON sml.item_id = i.id
                    JOIN `item_batches` b ON sml.batch_id = b.id
                    LEFT JOIN `locations` fl ON sml.from_location_id = fl.id
                    LEFT JOIN `locations` tl ON sml.to_location_id = tl.id
                    JOIN `users` u ON sml.created_by = u.id
                    ORDER BY sml.id DESC LIMIT 300";
            $stmt = $pdo->query($sql);
        } else {
            $sql = "SELECT sml.*, i.name AS item_name, i.item_code, b.batch_code, b.expiry_date,
                           fl.name AS from_location_name, tl.name AS to_location_name, u.full_name AS created_by_name
                    FROM `stock_movements_ledger` sml
                    JOIN `items` i ON sml.item_id = i.id
                    JOIN `item_batches` b ON sml.batch_id = b.id
                    LEFT JOIN `locations` fl ON sml.from_location_id = fl.id
                    LEFT JOIN `locations` tl ON sml.to_location_id = tl.id
                    JOIN `users` u ON sml.created_by = u.id
                    WHERE sml.from_location_id = ? OR sml.to_location_id = ?
                    ORDER BY sml.id DESC LIMIT 300";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([(int)$userLocId, (int)$userLocId]);
        }

        $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->json([
            'success'   => true,
            'movements' => $movements
        ]);
    }

    public function getExpiryAlerts() {
        $user = $this->requireAuth();
        $pdo = Model::getDB();

        $userLocId = $user['location_id'] ?? null;
        $isGlobalAdmin = ($user['role'] === 'ADMIN') || empty($userLocId);

        if ($isGlobalAdmin) {
            $sql = "SELECT b.*, i.name AS item_name, i.item_code, v.name AS vendor_name,
                           SUM(lbs.quantity_available) AS total_available_qty,
                           DATEDIFF(b.expiry_date, CURDATE()) AS days_to_expiry
                    FROM `item_batches` b
                    JOIN `items` i ON b.item_id = i.id
                    JOIN `vendors` v ON b.vendor_id = v.id
                    JOIN `location_batch_stock` lbs ON b.id = lbs.batch_id
                    WHERE lbs.quantity_available > 0 AND b.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
                    GROUP BY b.id
                    ORDER BY b.expiry_date ASC";
            $stmt = $pdo->query($sql);
        } else {
            $sql = "SELECT b.*, i.name AS item_name, i.item_code, v.name AS vendor_name,
                           SUM(lbs.quantity_available) AS total_available_qty,
                           DATEDIFF(b.expiry_date, CURDATE()) AS days_to_expiry
                    FROM `item_batches` b
                    JOIN `items` i ON b.item_id = i.id
                    JOIN `vendors` v ON b.vendor_id = v.id
                    JOIN `location_batch_stock` lbs ON b.id = lbs.batch_id
                    WHERE lbs.location_id = ? AND lbs.quantity_available > 0 AND b.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
                    GROUP BY b.id
                    ORDER BY b.expiry_date ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([(int)$userLocId]);
        }

        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->json([
            'success' => true,
            'alerts'  => $alerts
        ]);
    }

    public function getValuation() {
        $user = $this->requireAuth();
        $pdo = Model::getDB();

        $userLocId = $user['location_id'] ?? null;
        $isGlobalAdmin = ($user['role'] === 'ADMIN') || empty($userLocId);

        $retSubQuery = "LEFT JOIN (
            SELECT 
                sr.to_location_id,
                COUNT(DISTINCT sr.id) AS returned_count,
                SUM(COALESCE(sri.quantity, sri.qty)) AS returned_units,
                SUM(COALESCE(sri.total_amount, sri.subtotal, (COALESCE(sri.quantity, sri.qty) * COALESCE(sri.unit_rate, sri.unit_price)))) AS returned_value
            FROM `stock_returns` sr
            JOIN `stock_return_items` sri ON sr.id = sri.return_id
            WHERE sr.status IN ('ACCEPTED', 'PENDING_IN_WALLET')
            GROUP BY sr.to_location_id
        ) ret_summary ON l.id = ret_summary.to_location_id";

        if ($isGlobalAdmin) {
            $sql = "SELECT l.id AS location_id, l.name AS location_name, l.type AS location_type, l.code AS location_code,
                           COUNT(DISTINCT lbs.batch_id) AS total_batches,
                           COALESCE(SUM(lbs.quantity_available), 0) AS total_units,
                           COALESCE(SUM(lbs.quantity_available * b.purchase_price), 0) AS total_cost_valuation,
                           COALESCE(SUM(lbs.quantity_available * b.selling_price), 0) AS total_sales_valuation,
                           COALESCE(ret_summary.returned_units, 0) AS returned_units,
                           COALESCE(ret_summary.returned_value, 0) AS returned_value,
                           COALESCE(ret_summary.returned_count, 0) AS returned_count
                    FROM `locations` l
                    LEFT JOIN `location_batch_stock` lbs ON l.id = lbs.location_id
                    LEFT JOIN `item_batches` b ON lbs.batch_id = b.id
                    {$retSubQuery}
                    WHERE l.status = 'ACTIVE'
                    GROUP BY l.id
                    ORDER BY l.type ASC, l.name ASC";
            $stmt = $pdo->query($sql);
        } else {
            $sql = "SELECT l.id AS location_id, l.name AS location_name, l.type AS location_type, l.code AS location_code,
                           COUNT(DISTINCT lbs.batch_id) AS total_batches,
                           COALESCE(SUM(lbs.quantity_available), 0) AS total_units,
                           COALESCE(SUM(lbs.quantity_available * b.purchase_price), 0) AS total_cost_valuation,
                           COALESCE(SUM(lbs.quantity_available * b.selling_price), 0) AS total_sales_valuation,
                           COALESCE(ret_summary.returned_units, 0) AS returned_units,
                           COALESCE(ret_summary.returned_value, 0) AS returned_value,
                           COALESCE(ret_summary.returned_count, 0) AS returned_count
                    FROM `locations` l
                    LEFT JOIN `location_batch_stock` lbs ON l.id = lbs.location_id
                    LEFT JOIN `item_batches` b ON lbs.batch_id = b.id
                    {$retSubQuery}
                    WHERE l.id = ? AND l.status = 'ACTIVE'
                    GROUP BY l.id
                    ORDER BY l.type ASC, l.name ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([(int)$userLocId]);
        }

        $valuation = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->json([
            'success'   => true,
            'valuation' => $valuation
        ]);
    }

    /**
     * Admin Consolidated Item-Wise Inventory & Valuation Report
     */
    public function getConsolidatedItemValuation()
    {
        $user = $this->requireRoles(['ADMIN', 'STORE_MANAGER', 'AUDITOR']);
        $pdo = Model::getDB();

        $locParam = $_GET['location_id'] ?? $_GET['raw_location_id'] ?? '1';
        $decrypted = UrlSecurity::decrypt($locParam);
        $locFilter = ($decrypted !== false && $decrypted !== null) ? (string)$decrypted : (string)$locParam;

        $locId = 0;
        $isAllLocations = false;
        if (strtoupper(trim($locFilter)) === 'ALL') {
            $isAllLocations = true;
        } else {
            $locId = (int)$locFilter;
            if (!$locId) $locId = 1; // Default to Main Store
        }

        // Fetch location info
        $selectedLocation = null;
        if (!$isAllLocations) {
            $stmtLoc = $pdo->prepare("SELECT id, name, code, type FROM `locations` WHERE id = ?");
            $stmtLoc->execute([$locId]);
            $selectedLocation = $stmtLoc->fetch(PDO::FETCH_ASSOC);
        }

        // 1. Query On-Stock (active, non-expired stock)
        $onStockSql = "SELECT b.item_id,
                              COUNT(DISTINCT lbs.batch_id) AS batch_count,
                              COALESCE(SUM(lbs.quantity_available), 0) AS qty,
                              COALESCE(SUM(lbs.quantity_available * b.purchase_price), 0) AS cost_value,
                              COALESCE(SUM(lbs.quantity_available * b.selling_price), 0) AS sales_value
                       FROM `location_batch_stock` lbs
                       JOIN `item_batches` b ON lbs.batch_id = b.id
                       WHERE lbs.quantity_available > 0 AND (b.expiry_date IS NULL OR b.expiry_date >= CURDATE())";
        $onStockParams = [];
        if (!$isAllLocations) {
            $onStockSql .= " AND lbs.location_id = ?";
            $onStockParams[] = $locId;
        }
        $onStockSql .= " GROUP BY b.item_id";
        $stmtOnStock = $pdo->prepare($onStockSql);
        $stmtOnStock->execute($onStockParams);
        $onStockData = [];
        foreach ($stmtOnStock->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $onStockData[$row['item_id']] = $row;
        }

        // 2. Query Expired Stock (active stock where expiry_date < CURDATE())
        $expSql = "SELECT b.item_id,
                          COUNT(DISTINCT lbs.batch_id) AS batch_count,
                          COALESCE(SUM(lbs.quantity_available), 0) AS qty,
                          COALESCE(SUM(lbs.quantity_available * b.purchase_price), 0) AS cost_value,
                          COALESCE(SUM(lbs.quantity_available * b.selling_price), 0) AS sales_value
                   FROM `location_batch_stock` lbs
                   JOIN `item_batches` b ON lbs.batch_id = b.id
                   WHERE lbs.quantity_available > 0 AND (b.expiry_date IS NOT NULL AND b.expiry_date < CURDATE())";
        $expParams = [];
        if (!$isAllLocations) {
            $expSql .= " AND lbs.location_id = ?";
            $expParams[] = $locId;
        }
        $expSql .= " GROUP BY b.item_id";
        $stmtExp = $pdo->prepare($expSql);
        $stmtExp->execute($expParams);
        $expData = [];
        foreach ($stmtExp->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $expData[$row['item_id']] = $row;
        }

        // 3. Query Damaged Stock
        $dmgSql = "SELECT ds.item_id,
                          COUNT(DISTINCT ds.batch_id) AS batch_count,
                          COALESCE(SUM(ds.quantity), 0) AS qty,
                          COALESCE(SUM(ds.quantity * b.purchase_price), 0) AS cost_value,
                          COALESCE(SUM(ds.quantity * b.selling_price), 0) AS sales_value
                   FROM `damaged_stock` ds
                   JOIN `item_batches` b ON ds.batch_id = b.id";
        $dmgParams = [];
        if (!$isAllLocations) {
            $dmgSql .= " WHERE ds.location_id = ?";
            $dmgParams[] = $locId;
        }
        $dmgSql .= " GROUP BY ds.item_id";
        $stmtDmg = $pdo->prepare($dmgSql);
        $stmtDmg->execute($dmgParams);
        $dmgData = [];
        foreach ($stmtDmg->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $dmgData[$row['item_id']] = $row;
        }

        // 4. Query Received In Return Wallet (Pending Returns in Return Wallet)
        $walletSql = "SELECT sri.item_id,
                             COUNT(DISTINCT sri.batch_id) AS batch_count,
                             COALESCE(SUM(COALESCE(sri.quantity, sri.qty)), 0) AS qty,
                             COALESCE(SUM(COALESCE(sri.quantity, sri.qty) * b.purchase_price), 0) AS cost_value,
                             COALESCE(SUM(COALESCE(sri.total_amount, sri.subtotal, (COALESCE(sri.quantity, sri.qty) * b.selling_price))), 0) AS sales_value
                      FROM `stock_returns` sr
                      JOIN `stock_return_items` sri ON sr.id = sri.return_id
                      JOIN `item_batches` b ON sri.batch_id = b.id
                      WHERE sr.status IN ('PENDING_IN_WALLET', 'PENDING_ACCEPTANCE', 'PENDING', 'REJECTED')";
        $walletParams = [];
        if (!$isAllLocations) {
            $walletSql .= " AND (sr.from_location_id = ? OR sr.to_location_id = ?)";
            $walletParams[] = $locId;
            $walletParams[] = $locId;
        }
        $walletSql .= " GROUP BY sri.item_id";
        $stmtWallet = $pdo->prepare($walletSql);
        $stmtWallet->execute($walletParams);
        $walletData = [];
        foreach ($stmtWallet->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $walletData[$row['item_id']] = $row;
        }

        // Fetch All Items
        $stmtItems = $pdo->query("SELECT i.id, i.item_code, i.name, c.name AS category, i.unit_of_measure, i.min_reorder_level AS reorder_level
                                  FROM `items` i
                                  LEFT JOIN `categories` c ON i.category_id = c.id
                                  ORDER BY i.name ASC");
        $allItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        $consolidatedReport = [];

        foreach ($allItems as $item) {
            $itemId = (int)$item['id'];

            $onStock = $onStockData[$itemId] ?? ['batch_count' => 0, 'qty' => 0, 'cost_value' => 0, 'sales_value' => 0];
            $expired = $expData[$itemId] ?? ['batch_count' => 0, 'qty' => 0, 'cost_value' => 0, 'sales_value' => 0];
            $damaged = $dmgData[$itemId] ?? ['batch_count' => 0, 'qty' => 0, 'cost_value' => 0, 'sales_value' => 0];
            $wallet = $walletData[$itemId] ?? ['batch_count' => 0, 'qty' => 0, 'cost_value' => 0, 'sales_value' => 0];

            $totalBatches = (int)$onStock['batch_count'] + (int)$expired['batch_count'] + (int)$damaged['batch_count'] + (int)$wallet['batch_count'];

            $consolidatedReport[] = [
                'item_id' => $itemId,
                'item_code' => $item['item_code'],
                'item_name' => $item['name'],
                'category' => $item['category'],
                'unit_of_measure' => $item['unit_of_measure'],
                'total_batches_count' => $totalBatches,
                
                // 1. On Stock
                'on_stock_qty' => (float)$onStock['qty'],
                'on_stock_cost_value' => (float)$onStock['cost_value'],
                'on_stock_sales_value' => (float)$onStock['sales_value'],

                // 2. Expired Stock
                'expired_qty' => (float)$expired['qty'],
                'expired_cost_value' => (float)$expired['cost_value'],
                'expired_sales_value' => (float)$expired['sales_value'],

                // 3. Damaged Stock
                'damaged_qty' => (float)$damaged['qty'],
                'damaged_cost_value' => (float)$damaged['cost_value'],
                'damaged_sales_value' => (float)$damaged['sales_value'],

                // 4. In Return Wallet
                'wallet_qty' => (float)$wallet['qty'],
                'wallet_cost_value' => (float)$wallet['cost_value'],
                'wallet_sales_value' => (float)$wallet['sales_value'],

                // Grand Totals for Item
                'grand_total_cost_value' => (float)$onStock['cost_value'] + (float)$expired['cost_value'] + (float)$damaged['cost_value'] + (float)$wallet['cost_value'],
                'grand_total_sales_value' => (float)$onStock['sales_value'] + (float)$expired['sales_value'] + (float)$damaged['sales_value'] + (float)$wallet['sales_value']
            ];
        }

        $this->json([
            'success' => true,
            'location' => $selectedLocation ? $selectedLocation : ['id' => 0, 'name' => 'All Locations', 'code' => 'ALL', 'type' => 'ORGANIZATION'],
            'report' => $consolidatedReport
        ]);
    }

    /**
     * Consolidated Invoices & Credit Notes Report
     * Supports Date-Between filtering, Branch filtering, Opening Balance calculation, Credit Notes deduction, and Totals.
     */
    public function getConsolidatedInvoices()
    {
        $user = $this->requireAuth();
        $pdo = Model::getDB();

        $startDate = !empty($_GET['start_date']) ? trim($_GET['start_date']) : null;
        $endDate = !empty($_GET['end_date']) ? trim($_GET['end_date']) : null;

        $rawLocParam = $_GET['location_id'] ?? $_GET['raw_location_id'] ?? 'ALL';
        $decryptedLoc = UrlSecurity::decrypt($rawLocParam);
        $locFilter = ($decryptedLoc !== false && $decryptedLoc !== null) ? (string)$decryptedLoc : (string)$rawLocParam;

        $locId = 0;
        $isAllLocations = true;
        if (strtoupper(trim($locFilter)) !== 'ALL' && !empty($locFilter)) {
            $locId = (int)$locFilter;
            if ($locId > 0) {
                $isAllLocations = false;
            }
        }

        // Fetch Location metadata if specific location selected
        $selectedLocation = null;
        if (!$isAllLocations) {
            $stmtLoc = $pdo->prepare("SELECT id, name, code, type FROM `locations` WHERE id = ?");
            $stmtLoc->execute([$locId]);
            $selectedLocation = $stmtLoc->fetch(PDO::FETCH_ASSOC);
        }

        // 1. OPENING BALANCE CALCULATION (Transactions before $startDate)
        $openingBalance = [
            'has_opening_date'      => !empty($startDate),
            'opening_date'          => $startDate,
            'total_invoices_amount' => 0.00,
            'total_credit_notes'    => 0.00,
            'total_payments'        => 0.00,
            'opening_balance_due'   => 0.00
        ];

        if (!empty($startDate)) {
            // Prior Invoices Gross Amount before start_date
            $sqlPriorInv = "SELECT COALESCE(SUM(total_val), 0) AS total_inv
                            FROM `stock_transfers`
                            WHERE status != 'CANCELLED' AND transfer_type = 'BRANCH_INVOICED' AND DATE(COALESCE(dispatched_at, received_at)) < ?";
            $paramsPriorInv = [$startDate];
            if (!$isAllLocations) {
                $sqlPriorInv .= " AND to_location_id = ?";
                $paramsPriorInv[] = $locId;
            }
            $stmtPriorInv = $pdo->prepare($sqlPriorInv);
            $stmtPriorInv->execute($paramsPriorInv);
            $priorInvVal = (float)($stmtPriorInv->fetch(PDO::FETCH_ASSOC)['total_inv'] ?? 0.00);

            // Prior Payments recorded before start_date
            $sqlPriorPay = "SELECT COALESCE(SUM(amount_paid), 0) AS total_pay
                            FROM `invoice_payment_records`
                            WHERE DATE(created_at) < ?";
            $paramsPriorPay = [$startDate];
            if (!$isAllLocations) {
                $sqlPriorPay .= " AND transfer_id IN (SELECT id FROM `stock_transfers` WHERE to_location_id = ? AND transfer_type = 'BRANCH_INVOICED')";
                $paramsPriorPay[] = $locId;
            }
            $stmtPriorPay = $pdo->prepare($sqlPriorPay);
            $stmtPriorPay->execute($paramsPriorPay);
            $priorPayVal = (float)($stmtPriorPay->fetch(PDO::FETCH_ASSOC)['total_pay'] ?? 0.00);

            // Prior Credit Notes generated before start_date
            $sqlPriorCN = "SELECT COALESCE(SUM(total_amount), 0) AS total_cn
                           FROM `credit_notes`
                           WHERE DATE(created_at) < ?";
            $paramsPriorCN = [$startDate];
            if (!$isAllLocations) {
                $sqlPriorCN .= " AND branch_location_id = ?";
                $paramsPriorCN[] = $locId;
            }
            $stmtPriorCN = $pdo->prepare($sqlPriorCN);
            $stmtPriorCN->execute($paramsPriorCN);
            $priorCnVal = (float)($stmtPriorCN->fetch(PDO::FETCH_ASSOC)['total_cn'] ?? 0.00);

            $openingBalance['total_invoices_amount'] = round($priorInvVal, 3);
            $openingBalance['total_credit_notes']    = round($priorCnVal, 3);
            $openingBalance['total_payments']        = round($priorPayVal, 3);
            $openingBalance['opening_balance_due']   = round($priorInvVal - $priorCnVal - $priorPayVal, 3);
        }

        // 2. PERIOD INVOICES FETCHING
        $sqlInvoices = "SELECT st.*, COALESCE(st.dispatched_at, st.received_at) AS created_at,
                               fl.name AS from_location_name, fl.code AS from_location_code,
                               tl.name AS to_location_name, tl.code AS to_location_code,
                               u.full_name AS created_by_name
                        FROM `stock_transfers` st
                        JOIN `locations` fl ON st.from_location_id = fl.id
                        JOIN `locations` tl ON st.to_location_id = tl.id
                        JOIN `users` u ON st.created_by = u.id
                        WHERE st.transfer_type = 'BRANCH_INVOICED'";

        $paramsInv = [];

        if (!$isAllLocations) {
            $sqlInvoices .= " AND st.to_location_id = ?";
            $paramsInv[] = $locId;
        }

        if (!empty($startDate)) {
            $sqlInvoices .= " AND DATE(COALESCE(st.dispatched_at, st.received_at)) >= ?";
            $paramsInv[] = $startDate;
        }

        if (!empty($endDate)) {
            $sqlInvoices .= " AND DATE(COALESCE(st.dispatched_at, st.received_at)) <= ?";
            $paramsInv[] = $endDate;
        }


        $sqlInvoices .= " ORDER BY st.id DESC";

        $stmtInvoices = $pdo->prepare($sqlInvoices);
        $stmtInvoices->execute($paramsInv);
        $invoices = $stmtInvoices->fetchAll(PDO::FETCH_ASSOC);

        // Statements for Line Items, Credit Notes, and Payment Records
        $stmtItems = $pdo->prepare("SELECT sti.*, i.name AS item_name, i.item_code, i.unit_of_measure, b.batch_code, b.expiry_date
                                    FROM `stock_transfer_items` sti
                                    JOIN `items` i ON sti.item_id = i.id
                                    JOIN `item_batches` b ON sti.batch_id = b.id
                                    WHERE sti.transfer_id = ?");

        // Fetch ONLY Credit Notes explicitly linked to this invoice number or transfer number from credit_notes table
        $stmtCreditNotes = $pdo->prepare("SELECT cn.id AS credit_note_id, cn.credit_note_no, cn.created_at, cn.reason,
                                                 cn.total_amount AS credit_amount
                                          FROM `credit_notes` cn
                                          WHERE (cn.original_transfer_no = ? OR cn.original_transfer_no = ?)");

        $stmtPayRecords = $pdo->prepare("SELECT ipr.*, u.full_name AS created_by_name
                                        FROM `invoice_payment_records` ipr
                                        JOIN `users` u ON ipr.created_by = u.id
                                        WHERE ipr.transfer_id = ?
                                        ORDER BY ipr.id ASC");

        $totalSubtotal = 0.00;
        $totalVat = 0.00;
        $totalGrossInvoices = 0.00;
        $totalCreditNotes = 0.00;
        $totalNetInvoices = 0.00;
        $totalPaidAmount = 0.00;
        $totalBalanceDue = 0.00;

        $processedInvoices = [];

        foreach ($invoices as $inv) {
            $rawId = (int)$inv['id'];
            $invNo = $inv['invoice_no'] ?: $inv['transfer_no'];
            $trfNo = $inv['transfer_no'];
            $toLocId = (int)$inv['to_location_id'];

            $inv['raw_id'] = $rawId;
            $inv['raw_from_location_id'] = (int)$inv['from_location_id'];
            $inv['raw_to_location_id'] = $toLocId;
            $inv['id'] = UrlSecurity::encrypt($rawId);
            $inv['from_location_id'] = UrlSecurity::encrypt($inv['from_location_id']);
            $inv['to_location_id'] = UrlSecurity::encrypt($toLocId);

            $subtotal = (float)$inv['subtotal'];
            $grandTotal = (float)$inv['total_val'];
            if ($subtotal == 0.00 && $grandTotal > 0.00) {
                $subtotal = round($grandTotal / 1.10, 3);
                $vatAmount = round($grandTotal - $subtotal, 3);
            } else {
                $vatAmount = (float)($inv['vat_amount'] ?? 0.00);
            }

            $inv['subtotal'] = round($subtotal, 3);
            $inv['vat_amount'] = round($vatAmount, 3);
            $inv['total_val'] = round($grandTotal, 3);

            // Fetch Line Items
            $stmtItems->execute([$rawId]);
            $inv['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            // Fetch Credit Notes directly linked to THIS specific invoice
            $stmtCreditNotes->execute([$invNo, $trfNo]);
            $creditNotesList = $stmtCreditNotes->fetchAll(PDO::FETCH_ASSOC);
            
            $cnTotal = 0.00;
            foreach ($creditNotesList as $cn) {
                $cnTotal += (float)$cn['credit_amount'];
            }
            $cnTotal = round($cnTotal, 3);
            $inv['credit_notes'] = $creditNotesList;
            $inv['credit_note_amount'] = $cnTotal;

            $netAmount = max(0.00, round($grandTotal - $cnTotal, 3));
            $inv['net_amount'] = $netAmount;

            $paid = (float)($inv['paid_amount'] ?? 0.00);
            $inv['paid_amount'] = round($paid, 3);

            $pendingBalance = max(0.00, round($netAmount - $paid, 3));
            $inv['pending_balance'] = $pendingBalance;

            // Recalculate status dynamically based on net_amount vs paid_amount
            if ($paid >= $netAmount && $netAmount > 0) {
                $inv['payment_status'] = 'PAID';
            } else if ($paid > 0) {
                $inv['payment_status'] = 'PARTIAL';
            } else {
                $inv['payment_status'] = 'UNPAID';
            }

            // Fetch Payments History
            $stmtPayRecords->execute([$rawId]);
            $inv['payment_records'] = $stmtPayRecords->fetchAll(PDO::FETCH_ASSOC);

            // Accumulate Period Totals
            $totalSubtotal += $subtotal;
            $totalVat += $vatAmount;
            $totalGrossInvoices += $grandTotal;
            $totalCreditNotes += $cnTotal;
            $totalNetInvoices += $netAmount;
            $totalPaidAmount += $paid;
            $totalBalanceDue += $pendingBalance;

            $processedInvoices[] = $inv;
        }

        // Fetch Period Credit Notes Total & List from credit_notes table
        $sqlPeriodCNList = "SELECT cn.*, l.name AS branch_name, l.code AS branch_code, u.full_name AS created_by_name
                            FROM `credit_notes` cn
                            JOIN `locations` l ON cn.branch_location_id = l.id
                            JOIN `users` u ON cn.created_by = u.id
                            WHERE 1=1";
        $paramsPeriodCNList = [];
        if (!$isAllLocations) {
            $sqlPeriodCNList .= " AND cn.branch_location_id = ?";
            $paramsPeriodCNList[] = $locId;
        }
        if (!empty($startDate)) {
            $sqlPeriodCNList .= " AND DATE(cn.created_at) >= ?";
            $paramsPeriodCNList[] = $startDate;
        }
        if (!empty($endDate)) {
            $sqlPeriodCNList .= " AND DATE(cn.created_at) <= ?";
            $paramsPeriodCNList[] = $endDate;
        }
        $sqlPeriodCNList .= " ORDER BY cn.id DESC";

        $stmtPeriodCNList = $pdo->prepare($sqlPeriodCNList);
        $stmtPeriodCNList->execute($paramsPeriodCNList);
        $periodCreditNotesList = $stmtPeriodCNList->fetchAll(PDO::FETCH_ASSOC);

        $periodCnVal = 0.00;
        foreach ($periodCreditNotesList as &$cnItem) {
            $cnItem['raw_id'] = (int)$cnItem['id'];
            $cnItem['id'] = UrlSecurity::encrypt($cnItem['id']);
            $cnAmt = (float)$cnItem['total_amount'];
            $cnItem['total_amount'] = round($cnAmt, 3);
            $periodCnVal += $cnAmt;
        }
        $periodCnVal = round($periodCnVal, 3);

        $summary = [
            'total_invoices_count'        => count($processedInvoices),
            'total_subtotal'              => round($totalSubtotal, 3),
            'total_vat_amount'            => round($totalVat, 3),
            'total_gross_invoices'        => round($totalGrossInvoices, 3),
            'total_linked_credit_notes'   => round($totalCreditNotes, 3),
            'total_credit_notes'          => round($periodCnVal, 3),
            'total_net_invoices'          => round($totalNetInvoices, 3),
            'total_paid_amount'           => round($totalPaidAmount, 3),
            'period_outstanding_due'      => round($totalBalanceDue, 3),
            'closing_balance_due'         => round(($openingBalance['opening_balance_due'] ?? 0.00) + $totalBalanceDue, 3)
        ];


        $this->json([
            'success'          => true,
            'location'         => $selectedLocation ? $selectedLocation : ['id' => 0, 'name' => 'All Branches (Organization-Wide)', 'code' => 'ALL', 'type' => 'ORGANIZATION'],
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'opening_balance'  => $openingBalance,
            'summary'          => $summary,
            'invoices'         => $processedInvoices,
            'credit_notes'     => $periodCreditNotesList
        ]);
    }

}

