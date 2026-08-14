<?php
// Auth Controller for licensing server administrators
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/AdminModel.php';

class AuthController extends Controller {
    
    /**
     * Display Login Page
     */
    public function showLogin() {
        if (isset($_SESSION['admin_id'])) {
            $this->redirect('/');
        }
        $this->view('login', ['error' => null]);
    }

    /**
     * Process Login Post Request
     */
    public function login() {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $this->view('login', ['error' => 'Please enter username and password.']);
            return;
        }

        $admin = AdminModel::authenticate($username, $password);

        if ($admin) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $this->redirect('/');
        } else {
            $this->view('login', ['error' => 'Invalid credentials or inactive account.']);
        }
    }

    /**
     * Log out session
     */
    public function logout() {
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        session_destroy();
        $this->redirect('/login');
    }
}
