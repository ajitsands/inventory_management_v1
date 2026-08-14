<?php
// License Controller to generate, list, and toggle client license keys
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/LicenseModel.php';

class LicenseController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }

    /**
     * Dashboard: List all licenses
     */
    public function index() {
        $licenses = LicenseModel::getAll();
        $this->view('dashboard', ['licenses' => $licenses]);
    }

    /**
     * Display creation page
     */
    public function showCreate() {
        $this->view('create_license', [
            'error' => null,
            'default_key' => LicenseModel::generateLicenseKey()
        ]);
    }

    /**
     * Process license creation
     */
    public function create() {
        $customer = trim($_POST['customer_name'] ?? '');
        $app = trim($_POST['application_name'] ?? '');
        $domain = trim($_POST['domain_name'] ?? '');
        $ip = trim($_POST['ip_address'] ?? '');
        $expiry = trim($_POST['expiry_date'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        // Basic validations
        if (empty($customer) || empty($app) || empty($domain)) {
            $this->view('create_license', [
                'error' => 'Customer Name, Application Name, and Domain Name are required.',
                'default_key' => LicenseModel::generateLicenseKey()
            ]);
            return;
        }

        try {
            // 1. Generate unique License Key
            $licenseKey = LicenseModel::generateLicenseKey();
            
            // 2. Generate RSA Keypair natively
            $keypair = LicenseModel::generateKeyPair();

            // 3. Setup Data
            $data = [
                'license_key'      => $licenseKey,
                'customer_name'    => $customer,
                'application_name' => $app,
                'domain_name'      => $domain,
                'ip_address'       => !empty($ip) ? $ip : null,
                'private_key'      => $keypair['private'],
                'public_key'       => $keypair['public'],
                'expiry_date'      => !empty($expiry) ? $expiry : null, // If Blank/NULL, no expiry
                'status'           => $status
            ];

            // 4. Save to database
            LicenseModel::create($data);

            // Redirect back to dashboard
            $this->redirect('/');

        } catch (Exception $e) {
            $this->view('create_license', [
                'error' => 'Error generating license: ' . $e->getMessage(),
                'default_key' => LicenseModel::generateLicenseKey()
            ]);
        }
    }

    /**
     * Toggle License status (Active / Suspended)
     */
    public function toggleStatus() {
        $id = intval($_POST['id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');

        if ($id > 0 && in_array($newStatus, ['active', 'suspended', 'revoked'])) {
            LicenseModel::update($id, ['status' => $newStatus]);
        }

        $this->redirect('/');
    }

    /**
     * View keys & download public key details for customer
     */
    public function viewDetails() {
        $id = intval($_GET['id'] ?? 0);
        $license = LicenseModel::find($id);

        if (!$license) {
            $this->redirect('/');
        }

        $this->view('view_details', ['license' => $license]);
    }
}
