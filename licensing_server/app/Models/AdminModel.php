<?php
// Admin Login Model for Licensing Server
require_once __DIR__ . '/../../core/Model.php';

class AdminModel extends Model {
    protected static $table = 'licensing_admins';

    public static function authenticate($username, $password) {
        $admin = self::findWhere(['username' => $username]);
        if ($admin && $admin['status'] === 'active') {
            if (password_verify($password, $admin['password'])) {
                return $admin;
            }
        }
        return null;
    }
}
