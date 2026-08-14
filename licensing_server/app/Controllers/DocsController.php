<?php
// e:\inventory_system\licensing_server\app\Controllers\DocsController.php

require_once __DIR__ . '/../../core/Controller.php';

class DocsController extends Controller {
    public function index() {
        // We do not require auth for docs so developers can read them without a login
        // But you can add $this->requireAuth(); if you want them private.
        
        $this->view('docs');
    }
}
