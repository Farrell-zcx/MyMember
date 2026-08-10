<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Auth routes — login/register redirect ke SSO Engine
$routes->get('login', 'Auth::login');
$routes->post('login/process', 'Auth::login');   // Redirect ke SSO (form lokal dinonaktifkan)
$routes->get('register', 'Auth::register');       // Redirect ke SSO
$routes->post('register/process', 'Auth::register'); // Redirect ke SSO
$routes->get('logout', 'Auth::logout');

// SSO Callback — menerima redirect dari SSO Engine setelah login
$routes->get('auth/callback', 'SsoCallback::handle');

// Route dashboard mengarah ke halaman Dashboard Ringkasan
$routes->get('admin/dashboard', 'Dashboard::index');
$routes->get('admin/dashboard/live', 'Dashboard::getLiveStats');

// Route untuk Daftar Member (Read Only)
$routes->get('admin/member', 'Member::index');

// Route untuk Riwayat Kunjungan
$routes->get('admin/log-kunjungan', 'LogKunjungan::index');
$routes->get('admin/log-kunjungan/live', 'LogKunjungan::getLiveLogs');
$routes->get('admin/log-kunjungan/export/excel', 'LogKunjungan::exportExcel');
$routes->get('admin/log-kunjungan/export/pdf', 'LogKunjungan::exportPdf');

$routes->get('admin/session-fix', function () {
    session()->destroy();
    return redirect()->to('/login');
});

// GROUP ROUTE CRUD MASTER TIPE MEMBER
$routes->group('admin/member-type', function ($routes) {
    // Jalur GET untuk nampilin halaman
    $routes->get('/', 'MemberType::index');

    // Mengizinkan data POST masuk ke halaman index 
    $routes->post('/', 'MemberType::index');

    $routes->get('create', 'MemberType::create');
    $routes->post('store', 'MemberType::store');
    $routes->get('edit/(:num)', 'MemberType::edit/$1');
    $routes->post('update/(:num)', 'MemberType::update/$1');
    $routes->get('delete/(:num)', 'MemberType::delete/$1');
    $routes->post('scan-ocr', 'MemberType::scanOcr');
    $routes->get('poll-scan', 'MemberType::pollScanEvent');
});

$routes->get('ocr', 'OcrController::index');
$routes->post('ocr/scan', 'OcrController::scan');
$routes->post('ocr/checkin', 'OcrController::checkin');
$routes->get('ocr/get-member', 'OcrController::getMemberByNik');
$routes->post('ocr/update-cache', 'OcrController::updateCache');

// Kiosk View 
$routes->get('kiosk', 'Kiosk::index');
$routes->get('kiosk/checkTrigger', 'Kiosk::checkTrigger');
$routes->post('kiosk/streamFrame', 'Kiosk::streamFrame');

// Admin Kiosk Controller 
$routes->get('admin/kiosk', 'AdminKiosk::index');
$routes->post('admin/kiosk/trigger', 'AdminKiosk::trigger');
$routes->get('admin/kiosk/getStreamFrame', 'AdminKiosk::getStreamFrame');