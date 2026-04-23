<?php
//  Site config
define('SITE_URL',  rtrim(getenv('SITE_URL') ?: 'http://localhost/ecommerce', '/'));
define('SITE_NAME', 'World Compass');
define('CURRENCY',  'FCFA');

//  Database (Railway injecte MYSQL_HOST, MYSQL_USER, etc.)
define('DB_HOST', getenv('MYSQL_HOST')     ?: (getenv('DB_HOST') ?: 'localhost'));
define('DB_USER', getenv('MYSQL_USER')     ?: (getenv('DB_USER') ?: 'root'));
define('DB_PASS', getenv('MYSQL_PASSWORD') ?: (getenv('DB_PASS') ?: ''));
define('DB_NAME', getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: 'ecommerce'));
define('DB_PORT', (int)(getenv('MYSQL_PORT') ?: 3306));

//  Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//  Auto-migration : ajoute les colonnes et tables manquantes silencieusement
(function () {
    static $done = false;
    if ($done) return;
    $done = true;

    // Colonnes existantes
    try { db()->exec("ALTER TABLE products ADD COLUMN image VARCHAR(255) DEFAULT NULL"); } catch (Throwable) {}
    try { db()->exec("ALTER TABLE orders   ADD COLUMN payment_phone VARCHAR(20) DEFAULT NULL"); } catch (Throwable) {}

    // Portail vendeur
    try { db()->exec("ALTER TABLE users MODIFY COLUMN role ENUM('customer','seller','admin') DEFAULT 'customer'"); } catch (Throwable) {}
    try { db()->exec("ALTER TABLE users ADD COLUMN loyalty_points INT DEFAULT 0"); } catch (Throwable) {}
    try { db()->exec("ALTER TABLE users ADD COLUMN business_name VARCHAR(200) DEFAULT NULL"); } catch (Throwable) {}
    try { db()->exec("ALTER TABLE users ADD COLUMN seller_type ENUM('managed','autonomous') DEFAULT NULL"); } catch (Throwable) {}
    try { db()->exec("ALTER TABLE products ADD COLUMN seller_id INT DEFAULT NULL"); } catch (Throwable) {}

    // Ventes flash
    try { db()->exec("ALTER TABLE products ADD COLUMN flash_sale_price DECIMAL(10,2) DEFAULT NULL"); } catch (Throwable) {}
    try { db()->exec("ALTER TABLE products ADD COLUMN flash_sale_end DATETIME DEFAULT NULL"); } catch (Throwable) {}

    // Table demandes vendeurs
    try { db()->exec("CREATE TABLE IF NOT EXISTS seller_applications (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        user_id       INT NOT NULL,
        business_name VARCHAR(200) NOT NULL,
        description   TEXT,
        seller_type   ENUM('managed','autonomous') NOT NULL,
        status        ENUM('pending','approved','rejected') DEFAULT 'pending',
        admin_note    TEXT DEFAULT NULL,
        created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )"); } catch (Throwable) {}

    // Table commissions
    try { db()->exec("CREATE TABLE IF NOT EXISTS commissions (
        id               INT AUTO_INCREMENT PRIMARY KEY,
        order_id         INT NOT NULL,
        order_item_id    INT NOT NULL,
        seller_id        INT NOT NULL,
        product_name     VARCHAR(255),
        sale_amount      DECIMAL(10,2) NOT NULL,
        commission_rate  DECIMAL(5,2)  NOT NULL DEFAULT 8.00,
        commission_amount DECIMAL(10,2) NOT NULL,
        status           ENUM('pending','paid') DEFAULT 'pending',
        created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id)  REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (seller_id) REFERENCES users(id)  ON DELETE CASCADE
    )"); } catch (Throwable) {}

    // Fidélité — table transactions de points
    try { db()->exec("CREATE TABLE IF NOT EXISTS loyalty_transactions (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        user_id     INT NOT NULL,
        order_id    INT DEFAULT NULL,
        points      INT NOT NULL,
        type        ENUM('earn','redeem') NOT NULL,
        note        VARCHAR(255) DEFAULT NULL,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
    )"); } catch (Throwable) {}

    // Type de compte vendeur
    try { db()->exec("ALTER TABLE users ADD COLUMN account_type ENUM('individual','enterprise') DEFAULT NULL"); } catch (Throwable) {}
    try { db()->exec("ALTER TABLE seller_applications ADD COLUMN account_type ENUM('individual','enterprise') DEFAULT 'individual'"); } catch (Throwable) {}

    // Table commissions plateforme
    try { db()->exec("CREATE TABLE IF NOT EXISTS platform_commissions (
        id               INT AUTO_INCREMENT PRIMARY KEY,
        order_id         INT NOT NULL,
        seller_id        INT NOT NULL,
        sale_amount      DECIMAL(10,2) NOT NULL,
        commission_rate  DECIMAL(5,2)  NOT NULL,
        commission_amount DECIMAL(10,2) NOT NULL,
        status           ENUM('pending','paid') DEFAULT 'pending',
        created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id)  REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (seller_id) REFERENCES users(id)  ON DELETE CASCADE
    )"); } catch (Throwable) {}

    // Paramètres par défaut — frais d'ouverture & commission plateforme
    try { db()->exec("INSERT IGNORE INTO settings(setting_key,setting_value) VALUES
        ('opening_fee_individual','5000'),
        ('opening_fee_enterprise','20000'),
        ('platform_commission_rate','3.00'),
        ('platform_commission_enabled','1')"); } catch (Throwable) {}

    // Mise à jour du nom du site
    try {
        db()->exec("UPDATE settings SET setting_value='World Compass' WHERE setting_key='site_name'");
        db()->exec("UPDATE settings SET setting_value='Votre destination shopping mondiale' WHERE setting_key='site_tagline'");
    } catch (Throwable) {}
})();

//  PDO singleton 
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4",
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:2rem;background:#fee2e2;color:#991b1b;border-radius:8px;margin:2rem">
                <h3> Erreur de connexion à la base de données</h3>
                <p>'.$e->getMessage().'</p>
                <p>Vérifiez que MySQL est démarré dans XAMPP et que la base <strong>ecommerce</strong> existe.<br>
                Exécutez le fichier <strong>install.sql</strong> pour créer la base.</p>
            </div>');
        }
    }
    return $pdo;
}

//  Get site setting 
function setting(string $key, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        try {
            $s = db()->prepare("SELECT setting_value FROM settings WHERE setting_key=?");
            $s->execute([$key]);
            $cache[$key] = $s->fetchColumn() ?: $default;
        } catch (Throwable) {
            return $default;
        }
    }
    return $cache[$key];
}
