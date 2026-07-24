<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('login', 'Auth::login');
$routes->post('login/process', 'Auth::loginProcess');
$routes->get('register', 'Auth::register');
$routes->post('register/process', 'Auth::registerProcess');
$routes->get('logout', 'Auth::logout');

// Route dashboard mengarah ke halaman Dashboard Ringkasan
$routes->get('admin/dashboard', 'Dashboard::index');
$routes->get('admin/dashboard/live', 'Dashboard::getLiveStats');

// Route untuk Daftar Member (Read Only)
$routes->get('admin/member', 'Member::index');

// Route untuk Riwayat Kunjungan
$routes->get('admin/log-kunjungan', 'LogKunjungan::index');
$routes->get('admin/log-kunjungan/live', 'LogKunjungan::getLiveLogs');

// ROUTE SEMENTARA: Reset session jika ada bug cookie lama (hapus setelah selesai)
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