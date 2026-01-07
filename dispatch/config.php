<?php

// config.php
// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'insatcomar_usr');
define('DB_PASS', 'H~B(%rIjcGw-');
define('DB_NAME', 'insatcomar_dbctes');

// Timezone setting
define('APP_TIMEZONE', 'America/Argentina/Buenos_Aires');


define('LOGIN_USR', 'hola@insat.com.ar');
define('LOGIN_PASS', 'w[GY9q511Cf4');

// Scraper Configuration
define('SCRAPER_LOGIN_URL', 'https://portal.orbith.com/admin/admin/index.php');
const SCRAPER_URLS = [
	"https://portal.orbith.com/admin/admin/index.php?operation=consumidorView&status=4",
	"https://portal.orbith.com/admin/admin/index.php?operation=consumidorView&status=5"
];

// !!! SECURITY WARNING: Storing these directly in a file, even config.php, is not ideal for high security.
// Consider environment variables for production.
define('SCRAPER_USERNAME', 'hola@insat.com.ar');
define('SCRAPER_PASSWORD', 'gVtb9N9bsmmN!2G');

define('SCRAPER_RESULTS_PER_PAGE', 30);

// Plan Mapping (if static and global)
const PLANES_MAP = [
    'Entry' => ['Entry'],
    'Weekend 10' => ['FAMILY 10'],
    'Family 40' => ['Prestador - Premium 40 - 30x6','FAMILY 40'],
    'Family 100' => ['Prestador - Infinity 100 - 30x6','FAMILY 100'],
    'Professional 50' => ['Prestador - Pro 50 - 30x6','Pro 50 | 20x3','PROFESSIONAL 50'],
    'Professional 80' => ['Prestador - Pro 80 - 30x6','Pro 80 | 20x3','PROFESSIONAL 80'],
    'Professional 120' => ['Prestador - Pro 120 - 30x6','Pro 120 | 20x3','PROFESSIONAL 120'],
    'Pyme 50' => ['Prestador - Pyme 50 - 30x6','Pyme 50 | 20x3','Pyme 50GB'],
    'Pyme 80' => ['Prestador - Pyme 80 - 30x6','Pyme 80 | 20x3','Pyme 80 GB'],
    'Pyme 120' => ['Prestador - Pyme 120 - 30x6','Pyme 120 | 20x3','Pyme 120GB'],
    'Agro 150' => ['Business 150+'],
    'Agro 220' => ['Business 220+'],
    'Agro 280' => ['Business 280+'],
    'Estandar' => ['Prestador - Standard - 30x6','Standard 20%x3 meses','Standard'],
    'Priorizado 80' => ['Prestador - Priorizado 80 - 30x6','Priorizado 80 20%x3 meses','Priorizado 80 Gb'],
    'Priorizado 200' => ['Prestador - Priorizado 200 - 30x6','Priorizado 200 20%x3 meses','Priorizado 200 GB'],
];

// --- Authentication Configuration ---
define('AUTH_USERNAME', 'admin'); // Your desired username
// IMPORTANT: Replace 'your_hashed_password_here' with an actual hashed password.
// Use password_hash('your_secret_password', PASSWORD_DEFAULT) to generate it.
define('AUTH_PASSWORD_HASH', '$2y$10$7nYjr.dHt0II9990FHo6FOP4S0q6bIMYBgfv/HUjaIRV5RD287OMK'); // Example hash for 'password123'
// Example hash for 'password123': $2y$10$tD.YQ8.uP5hV7Y1w.M/Q.e0.J7L5m2d5w2g1t5l1w4h4.J4p1l2w2n3
// Replace with a hash generated for your actual desired password.