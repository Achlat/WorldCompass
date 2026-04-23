-- ============================================================
-- World Compass – E-Commerce Application
-- Base de données : world2784361
-- ============================================================

CREATE DATABASE IF NOT EXISTS world2784361
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE world2784361;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items, orders, cart, reviews, products, categories, users, settings;
SET FOREIGN_KEY_CHECKS = 1;

--  USERS 
CREATE TABLE users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  firstname   VARCHAR(100) NOT NULL,
  lastname    VARCHAR(100) NOT NULL,
  email       VARCHAR(150) UNIQUE NOT NULL,
  password    VARCHAR(255) NOT NULL,
  phone       VARCHAR(20),
  address     TEXT,
  city        VARCHAR(100),
  role        ENUM('customer','admin') DEFAULT 'customer',
  active      TINYINT(1) DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--  CATEGORIES 
CREATE TABLE categories (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  parent_id   INT DEFAULT NULL,
  name        VARCHAR(100) NOT NULL,
  slug        VARCHAR(100) UNIQUE NOT NULL,
  description TEXT,
  icon        VARCHAR(50) DEFAULT '',
  color       VARCHAR(20) DEFAULT '#5469d4',
  sort_order  INT DEFAULT 0,
  active      TINYINT(1) DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

--  PRODUCTS
CREATE TABLE products (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT,
  name        VARCHAR(255) NOT NULL,
  slug        VARCHAR(255) UNIQUE NOT NULL,
  description TEXT,
  price       DECIMAL(10,2) NOT NULL,
  old_price   DECIMAL(10,2) DEFAULT NULL,
  stock       INT DEFAULT 0,
  image_color VARCHAR(30) DEFAULT '#5469d4',
  image       VARCHAR(255) DEFAULT NULL,
  featured    TINYINT(1) DEFAULT 0,
  active      TINYINT(1) DEFAULT 1,
  views       INT DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

--  ORDERS 
CREATE TABLE orders (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT,
  order_number    VARCHAR(20) UNIQUE NOT NULL,
  firstname       VARCHAR(100),
  lastname        VARCHAR(100),
  email           VARCHAR(150),
  phone           VARCHAR(20),
  address         TEXT,
  city            VARCHAR(100),
  subtotal        DECIMAL(10,2) NOT NULL,
  shipping        DECIMAL(10,2) DEFAULT 0,
  total           DECIMAL(10,2) NOT NULL,
  payment_method  VARCHAR(50) DEFAULT 'cash',
  payment_phone   VARCHAR(20) DEFAULT NULL,
  status          ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  notes           TEXT,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

--  ORDER ITEMS 
CREATE TABLE order_items (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  order_id      INT NOT NULL,
  product_id    INT,
  product_name  VARCHAR(255),
  product_price DECIMAL(10,2),
  quantity      INT NOT NULL,
  subtotal      DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

--  CART 
CREATE TABLE cart (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT,
  session_id  VARCHAR(100),
  product_id  INT NOT NULL,
  quantity    INT NOT NULL DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

--  REVIEWS 
CREATE TABLE reviews (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  product_id  INT NOT NULL,
  user_id     INT,
  user_name   VARCHAR(100),
  rating      TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment     TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE SET NULL
);

--  SETTINGS 
CREATE TABLE settings (
  setting_key   VARCHAR(100) PRIMARY KEY,
  setting_value TEXT,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- DEMO DATA
-- ============================================================

-- Admin: admin@shop.com / admin123
-- User:  user@shop.com  / user123
INSERT INTO users (firstname,lastname,email,password,phone,address,city,role) VALUES
('Admin','World Compass','admin@shop.com','$2y$10$ymIpJUvJom4xrfhTvgAZxeqETxDNMrAv8exqkUNbgdAbEyWGLaxaG','','','','admin'),
('Kofi','Mensah','user@shop.com','$2y$10$3bucj.W.wI.To0NB9ZRGH.KDtzl3RSJrXu5.OrNqMcYJWmBrZ2Ebu','','','','customer'),
('Ama','Foli','ama.foli@email.com','$2y$10$3bucj.W.wI.To0NB9ZRGH.KDtzl3RSJrXu5.OrNqMcYJWmBrZ2Ebu','','','','customer');

-- Categories
INSERT INTO categories (name,slug,description,icon,color,sort_order) VALUES
('Électronique',    'electronique',   'Smartphones, ordinateurs, accessoires tech',    '','#5469d4',1),
('Mode & Vêtements','mode',           'Vêtements, chaussures, accessoires de mode',     '','#e94560',2),
('Maison & Déco',   'maison',         'Mobilier, décoration, électroménager',           '','#10b981',3),
('Sport & Loisirs', 'sport',          'Équipement sportif, jeux, plein air',            '','#f59e0b',4),
('Beauté & Santé',  'beaute',         'Cosmétiques, soins, santé',                      '','#8b5cf6',5),
('Alimentation',    'alimentation',   'Épicerie, boissons, produits locaux',            '','#06b6d4',6);

-- Products – Électronique (cat 1)
INSERT INTO products (category_id,name,slug,description,price,old_price,stock,image_color,featured) VALUES
(1,'Smartphone ProMax 13','smartphone-promax-13','Écran AMOLED 6.7", 256 Go, 5G, triple caméra 108MP. Performance exceptionnelle.',285000,350000,15,'#5469d4',1),
(1,'Casque Bluetooth Premium','casque-bluetooth','Son Hi-Fi, réduction de bruit active, 30h d''autonomie, pliable.',45000,60000,30,'#1a1a2e',1),
(1,'Tablette 10 pouces','tablette-10','Écran Full HD, 64 Go, WiFi+4G, stylus inclus. Idéale pour étudier.',120000,145000,20,'#0f3460',0),
(1,'Laptop UltraBook 14"','laptop-ultrabook','Intel i5 12e gen, 8 Go RAM, 512 Go SSD, 8h batterie.',420000,499000,8,'#312e81',1),
(1,'Écouteurs Sans Fil','ecouteurs-sans-fil','True Wireless, IPX5, 24h autonomie, boitier de charge.',22000,30000,50,'#1e3a5f',0),
(1,'Caméra de Surveillance','camera-surveillance','Full HD 1080p, WiFi, vision nocturne, détection mouvement.',35000,48000,25,'#0c4a6e',0);

-- Products – Mode (cat 2)
INSERT INTO products (category_id,name,slug,description,price,old_price,stock,image_color,featured) VALUES
(2,'Robe Élégante Soirée','robe-elegante','Tissu satiné, coupe moderne, disponible en 5 couleurs. Tailles S à XXL.',28000,38000,40,'#e94560',1),
(2,'Costume Homme 3 Pièces','costume-homme','Tissu premium, coupe slim, ceinture offerte. Couleur anthracite.',75000,95000,20,'#334155',0),
(2,'Sneakers Urban Style','sneakers-urban','Semelle amortissante, respirant, unisex, tailles 36-46.',32000,42000,60,'#e94560',1),
(2,'Sac à Main Cuir','sac-main-cuir','Cuir véritable, plusieurs compartiments, bandoulière réglable.',55000,72000,15,'#7c3aed',0),
(2,'Montre Élégante Dorée','montre-elegante','Mouvement quartz, bracelet acier, étanche 30m.',48000,65000,12,'#d97706',1),
(2,'Lunettes de Soleil UV400','lunettes-soleil','Protection UV400, monture légère, housse incluse.',12000,18000,80,'#1e40af',0);

-- Products – Maison (cat 3)
INSERT INTO products (category_id,name,slug,description,price,old_price,stock,image_color,featured) VALUES
(3,'Canapé 3 Places Moderne','canape-3-places','Tissu velours, pieds bois massif, livraison incluse.',185000,240000,5,'#10b981',1),
(3,'Lampe LED Design','lampe-led-design','Variateur d''intensité, 3 tons de lumière, bras articulé.',18000,25000,35,'#065f46',0),
(3,'Robot Mixeur Pro','robot-mixeur','1200W, 8 vitesses, bol 2L inox, accessoires variés.',42000,58000,22,'#047857',1),
(3,'Cadre Décoratif Lot 3','cadre-decoratif','Lot de 3 cadres, verre trempé, finition dorée, 20×30cm.',15000,22000,45,'#6d28d9',0);

-- Products – Sport (cat 4)
INSERT INTO products (category_id,name,slug,description,price,old_price,stock,image_color,featured) VALUES
(4,'Vélo VTT 26 Pouces','velo-vtt','21 vitesses Shimano, fourche suspendue, freins disque.',145000,180000,8,'#f59e0b',1),
(4,'Tapis de Yoga Premium','tapis-yoga','Antidérapant, épaisseur 8mm, sangle de transport, 183×61cm.',12000,18000,60,'#92400e',0),
(4,'Haltères Réglables 20kg','halteres-reglables','Jeu de 2, réglables 2-10kg, poignée ergonomique.',38000,50000,20,'#78350f',1),
(4,'Raquette de Tennis Pro','raquette-tennis','Cadre carbone, cordes incluses, housse de protection.',25000,35000,15,'#d97706',0);

-- Products – Beauté (cat 5)
INSERT INTO products (category_id,name,slug,description,price,old_price,stock,image_color,featured) VALUES
(5,'Coffret Parfum Luxe','coffret-parfum','Eau de parfum 100ml + lotion 200ml, notes florales, tenue 12h.',38000,52000,25,'#8b5cf6',1),
(5,'Sérum Vitamine C 30ml','serum-vitamine-c','Anti-âge, éclat, formule concentrée, pour tous types de peau.',22000,30000,40,'#7c3aed',0),
(5,'Sèche-cheveux 2200W','seche-cheveux','Ionique, 3 températures, concentrateur et diffuseur inclus.',28000,38000,18,'#6d28d9',1);

-- Products – Alimentation (cat 6)
INSERT INTO products (category_id,name,slug,description,price,old_price,stock,image_color,featured) VALUES
(6,'Café Arabica Premium 1kg','cafe-arabica','Grains sélectionnés, torréfaction artisanale, origine Éthiopie.',15000,20000,100,'#92400e',1),
(6,'Huile d''Olive Extra Vierge','huile-olive-2l','2 litres, première pression à froid, acidité < 0.3%.',18000,24000,55,'#65a30d',0),
(6,'Miel Naturel Pur 500g','miel-naturel','Récolte locale, non chauffé, certifié pur, richement aromatique.',8500,12000,80,'#d97706',1);

-- Demo orders
INSERT INTO orders (user_id,order_number,firstname,lastname,email,phone,address,city,subtotal,shipping,total,status) VALUES
(2,'ORD-2025-0001','Kofi','Mensah','user@shop.com','90000002','Quartier Tokoin','Lomé',307000,0,307000,'delivered'),
(2,'ORD-2025-0002','Kofi','Mensah','user@shop.com','90000002','Quartier Tokoin','Lomé',45000,2000,47000,'processing'),
(3,'ORD-2025-0003','Ama','Foli','ama.foli@email.com','90000003','Bè Kpota','Lomé',28000,2000,30000,'pending');

INSERT INTO order_items (order_id,product_id,product_name,product_price,quantity,subtotal) VALUES
(1,1,'Smartphone ProMax 13',285000,1,285000),
(1,5,'Écouteurs Sans Fil',22000,1,22000),
(2,2,'Casque Bluetooth Premium',45000,1,45000),
(3,7,'Robe Élégante Soirée',28000,1,28000);

-- Reviews
INSERT INTO reviews (product_id,user_id,user_name,rating,comment) VALUES
(1,2,'Kofi M.',5,'Excellent smartphone, très rapide et belle qualité photo !'),
(1,3,'Ama F.',4,'Très bonne qualité, batterie tient bien. Je recommande.'),
(2,2,'Kofi M.',5,'Son incroyable, réduction de bruit parfaite pour le bureau.'),
(7,3,'Ama F.',5,'Très belle robe, tissu de qualité, livraison rapide !'),
(9,2,'Kofi M.',4,'Confortables et stylées, correspond bien à la description.');

-- Settings
INSERT INTO settings (setting_key,setting_value) VALUES
('site_name','World Compass'),
('site_tagline','Votre Shopping en Toute Confiance'),
('site_email','contact@world-compass.com'),
('site_phone','+228 90 78 28 96'),
('site_address','Rue du Commerce, Lomé, Togo'),
('currency','FCFA'),
('shipping_cost','2000'),
('free_shipping_threshold','50000'),
('items_per_page','12'),
('maintenance_mode','0');
