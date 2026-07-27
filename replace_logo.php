<?php
$files = [
    'app/Views/welcome_message.php',
    'app/Views/auth/login.php',
    'app/Views/auth/register.php',
    'app/Views/admin/dashboard.php',
    'app/Views/admin/member_type/index.php',
    'app/Views/admin/member_type/create.php',
    'app/Views/admin/member_type/edit.php',
    'app/Views/admin/log_kunjungan.php',
    'app/Views/admin/member.php',
    'app/Views/upload_ktp.php'
];

foreach ($files as $file) {
    $path = "d:/laragon/www/mymember/" . $file;
    $content = file_get_contents($path);
    $original = $content;

    if ($file == 'app/Views/welcome_message.php') {
        $content = preg_replace('/<div class="flex items-center gap-2">\s*<span class="material-symbols-outlined[^>]+>shield_person<\/span>\s*<h1[^>]+>MyMember<\/h1>\s*<\/div>/is', '<div class="flex items-center gap-2">\n            <img src="<?= base_url(\'images/logo.png\') ?>" alt="MyMember Logo" class="h-10 w-auto" />\n        </div>', $content);
    }
    
    if ($file == 'app/Views/auth/login.php') {
        $content = preg_replace('/<div class="text-center mb-xl">\s*<div[^>]+>\s*<span[^>]+>shield_person<\/span>\s*<\/div>\s*<h1[^>]+>MyMember<\/h1>\s*<p[^>]+>Admin Portal Access<\/p>\s*<\/div>/is', '<div class="text-center mb-xl flex flex-col items-center">\n                <img src="<?= base_url(\'images/logo.png\') ?>" alt="MyMember Logo" class="h-20 w-auto mb-4" />\n                <p class="font-body-md text-body-md text-on-surface-variant mt-xs">Admin Portal Access</p>\n            </div>', $content);
    }
    
    if ($file == 'app/Views/auth/register.php') {
        $content = preg_replace('/<div class="text-center mb-xl">\s*<div[^>]+>\s*<span[^>]+>person_add<\/span>\s*<\/div>\s*<h1[^>]+>MyMember<\/h1>\s*<p[^>]+>Register New Admin Portal Account<\/p>\s*<\/div>/is', '<div class="text-center mb-xl flex flex-col items-center">\n                <img src="<?= base_url(\'images/logo.png\') ?>" alt="MyMember Logo" class="h-20 w-auto mb-4" />\n                <p class="font-body-md text-body-md text-on-surface-variant mt-xs">Register New Admin Portal Account</p>\n            </div>', $content);
    }
    
    if (strpos($file, 'admin/') !== false) {
        $content = preg_replace('/<div class="px-6 mb-10">\s*<h1[^>]+>MyMember<\/h1>\s*<p[^>]+>Admin Portal<\/p>\s*<\/div>/is', '<div class="px-6 mb-10">\n        <img src="<?= base_url(\'images/logo.png\') ?>" alt="MyMember Logo" class="h-12 w-auto mb-2" />\n        <p class="text-label-sm text-on-surface-variant opacity-70">Admin Portal</p>\n    </div>', $content);
    }
    
    if ($file == 'app/Views/upload_ktp.php') {
        $content = preg_replace('/<!-- Logo\/Header -->\s*<div class="text-center mb-8">\s*<h1[^>]+>MyMember<\/h1>\s*<p[^>]+>Self-Service KTP Check-in<\/p>\s*<\/div>/is', '<!-- Logo/Header -->\n        <div class="text-center mb-8 flex flex-col items-center">\n            <img src="<?= base_url(\'images/logo.png\') ?>" alt="MyMember Logo" class="h-16 w-auto mb-2" />\n            <p class="text-sm text-gray-500 mt-1">Self-Service KTP Check-in</p>\n        </div>', $content);
    }

    if ($original !== $content) {
        file_put_contents($path, $content);
        echo "Updated $file\n";
    } else {
        echo "Failed to match $file\n";
    }
}
?>
