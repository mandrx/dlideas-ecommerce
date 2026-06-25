-- =============================================================
-- CI3 Ecomm — Development Seed Data
-- Run AFTER schema.sql
-- =============================================================
USE ci3_ecomm;

-- ---------------------------------------------------------------
-- Users (sellers + buyers)
-- All passwords: Test@1234
-- Hash: $2y$10$.kRloWjX6vH7s3PpdPPym.PKErlAwlKd3KHK4Ubqzck0ddLOJX1Om
-- ---------------------------------------------------------------
INSERT INTO `users` (`email`, `password`, `full_name`, `phone`, `role`, `status`) VALUES
('mandrx@gmail.com',    '$2y$10$.kRloWjX6vH7s3PpdPPym.PKErlAwlKd3KHK4Ubqzck0ddLOJX1Om', 'Hafiz',          NULL,           'owner',  'active'),
('alice@example.com',   '$2y$10$.kRloWjX6vH7s3PpdPPym.PKErlAwlKd3KHK4Ubqzck0ddLOJX1Om', 'Alice Nguyen',   '081234567890', 'seller', 'active'),
('bob@example.com',     '$2y$10$.kRloWjX6vH7s3PpdPPym.PKErlAwlKd3KHK4Ubqzck0ddLOJX1Om', 'Bob Rahman',     '082345678901', 'seller', 'active'),
('carol@example.com',   '$2y$10$.kRloWjX6vH7s3PpdPPym.PKErlAwlKd3KHK4Ubqzck0ddLOJX1Om', 'Carol Santoso',  '083456789012', 'seller', 'active'),
('dave@example.com',    '$2y$10$.kRloWjX6vH7s3PpdPPym.PKErlAwlKd3KHK4Ubqzck0ddLOJX1Om', 'Dave Kusuma',    NULL,           'buyer',  'active'),
('eve@example.com',     '$2y$10$.kRloWjX6vH7s3PpdPPym.PKErlAwlKd3KHK4Ubqzck0ddLOJX1Om', 'Eve Wijaya',     NULL,           'buyer',  'active'),
('frank@example.com',   '$2y$10$.kRloWjX6vH7s3PpdPPym.PKErlAwlKd3KHK4Ubqzck0ddLOJX1Om', 'Frank Banned',   NULL,           'buyer',  'banned');

-- ---------------------------------------------------------------
-- Stores (one per seller)
-- ---------------------------------------------------------------
-- alice -> store id 1
-- bob   -> store id 2
-- carol -> store id 3 (suspended, to test that scenario)
INSERT INTO `stores` (`user_id`, `name`, `slug`, `description`, `status`) VALUES
(2, 'Alice Tech Store',   'alice-tech-store',   'Gadgets and accessories for everyday life.',       'active'),
(3, 'Bob Fashion Hub',    'bob-fashion-hub',    'Trending clothes and footwear for all ages.',      'active'),
(4, 'Carol Craft Corner', 'carol-craft-corner', 'Handmade goods and art supplies.',                 'suspended');

-- ---------------------------------------------------------------
-- Categories (parent + children)
-- ---------------------------------------------------------------
INSERT INTO `categories` (`parent_id`, `name`, `slug`, `sort_order`) VALUES
(NULL, 'Electronics',    'electronics',    1),
(NULL, 'Fashion',        'fashion',        2),
(NULL, 'Home & Living',  'home-living',    3),
(NULL, 'Art & Crafts',   'art-crafts',     4);

-- Sub-categories
INSERT INTO `categories` (`parent_id`, `name`, `slug`, `sort_order`) VALUES
(1, 'Smartphones',    'smartphones',    1),
(1, 'Accessories',    'accessories',    2),
(2, 'Men\'s Clothing','mens-clothing',  1),
(2, 'Women\'s Shoes', 'womens-shoes',   2),
(3, 'Lighting',       'lighting',       1),
(4, 'Stationery',     'stationery',     1);

-- ---------------------------------------------------------------
-- Products — Alice Tech Store (store_id = 1)
-- ---------------------------------------------------------------
INSERT INTO `products` (`store_id`, `category_id`, `name`, `slug`, `description`, `price`, `sale_price`, `stock`, `weight`, `status`) VALUES

-- 1. Active, on sale, good stock
(1, 5, 'Wireless Bluetooth Earbuds Pro',
 'wireless-bluetooth-earbuds-pro',
 'Premium noise-cancelling earbuds with 30-hour battery life. IPX5 water resistance. Supports multipoint connection to two devices simultaneously.',
 499000.00, 349000.00, 45, 85.00, 'active'),

-- 2. Active, no sale, limited stock
(1, 5, 'USB-C Fast Charger 65W GaN',
 'usb-c-fast-charger-65w-gan',
 'Compact GaN charger with 65W output. Charges laptops, tablets and phones simultaneously via dual USB-C ports.',
 199000.00, NULL, 3, 120.00, 'active'),

-- 3. Active, high price, ample stock
(1, 6, 'Mechanical Keyboard TKL RGB',
 'mechanical-keyboard-tkl-rgb',
 'Tenkeyless mechanical keyboard with Gateron Blue switches and per-key RGB lighting. PBT double-shot keycaps.',
 850000.00, 720000.00, 20, 780.00, 'active'),

-- 4. Inactive (pulled from shelves)
(1, 6, 'Webcam 1080p AutoFocus',
 'webcam-1080p-autofocus',
 'Full HD webcam with auto-focus and built-in stereo microphone. Plug and play, no drivers required.',
 275000.00, NULL, 0, 200.00, 'inactive'),

-- 5. Draft (not yet published)
(1, 5, 'TWS Bone Conduction Headset',
 'tws-bone-conduction-headset',
 'Open-ear bone conduction headphones. Safe for running, cycling and outdoor sports.',
 620000.00, NULL, 15, 60.00, 'draft');

-- ---------------------------------------------------------------
-- Products — Bob Fashion Hub (store_id = 2)
-- ---------------------------------------------------------------
INSERT INTO `products` (`store_id`, `category_id`, `name`, `slug`, `description`, `price`, `sale_price`, `stock`, `weight`, `status`) VALUES

-- 6. Active, sale, high stock
(2, 7, 'Classic Slim-Fit Chinos',
 'classic-slim-fit-chinos',
 'Stretch-cotton slim-fit chinos. Available in khaki, navy, and olive. Comfortable for office and casual wear.',
 320000.00, 249000.00, 120, 450.00, 'active'),

-- 7. Active, no sale
(2, 8, 'Women\'s Running Sneakers Lite',
 'womens-running-sneakers-lite',
 'Lightweight EVA foam sole with breathable mesh upper. Ideal for daily jogging and gym sessions.',
 410000.00, NULL, 56, 380.00, 'active'),

-- 8. Active, on sale, low stock edge case (stock = 1)
(2, 7, 'Oversized Graphic Tee — Limited Drop',
 'oversized-graphic-tee-limited-drop',
 'Limited-edition oversized tee with original screen-print design. 100% ring-spun cotton. Unisex sizing.',
 175000.00, 149000.00, 1, 220.00, 'active'),

-- 9. Draft
(2, 8, 'Leather Oxford Shoes Brown',
 'leather-oxford-shoes-brown',
 'Genuine leather oxford with cushioned insole. Hand-stitched welt construction.',
 890000.00, NULL, 10, 900.00, 'draft');

-- ---------------------------------------------------------------
-- Products — Carol Craft Corner (store_id = 3, suspended)
-- Products exist but the store is suspended — useful for testing access control
-- ---------------------------------------------------------------
INSERT INTO `products` (`store_id`, `category_id`, `name`, `slug`, `description`, `price`, `sale_price`, `stock`, `weight`, `status`) VALUES

-- 10. Active product in a suspended store
(3, 10, 'Bullet Journal Set A5',
 'bullet-journal-set-a5',
 'Dotted A5 notebook with 200gsm acid-free pages, included micro-pen set and sticker sheets.',
 145000.00, NULL, 30, 310.00, 'active'),

-- 11. Inactive in suspended store
(3, 4,  'Watercolour Paint Set 24 Colours',
 'watercolour-paint-set-24-colours',
 'Professional watercolour set with 24 vibrant pigments. Ideal for beginners and hobbyists.',
 98000.00, 79000.00, 0, 260.00, 'inactive');

-- ---------------------------------------------------------------
-- Product Images (using placeholder paths; replace with real uploads)
-- ---------------------------------------------------------------
INSERT INTO `product_images` (`product_id`, `image_path`, `is_primary`, `sort_order`) VALUES
-- Earbuds (product 1)
(1, 'uploads/products/earbuds-pro-main.jpg',  1, 0),
(1, 'uploads/products/earbuds-pro-case.jpg',  0, 1),
(1, 'uploads/products/earbuds-pro-wear.jpg',  0, 2),
-- USB-C Charger (product 2)
(2, 'uploads/products/gan-charger-main.jpg',  1, 0),
-- Keyboard (product 3)
(3, 'uploads/products/keyboard-rgb-main.webp', 1, 0),
(3, 'uploads/products/keyboard-rgb-side.webp', 0, 1),
-- Chinos (product 6)
(6, 'uploads/products/chinos-khaki-main.webp', 1, 0),
(6, 'uploads/products/chinos-navy.jpg',       0, 1),
-- Sneakers (product 7)
(7, 'uploads/products/sneakers-main.jpg',     1, 0),
-- Graphic Tee (product 8)
(8, 'uploads/products/tee-front.jpg',         1, 0),
(8, 'uploads/products/tee-back.webp',          0, 1),
-- Bullet Journal (product 10)
(10,'uploads/products/journal-main.webp',      1, 0);

-- ---------------------------------------------------------------
-- Categories — Sports (parent + sub-categories)
-- ---------------------------------------------------------------
INSERT INTO `categories` (`parent_id`, `name`, `slug`, `sort_order`) VALUES
(NULL, 'Sports', 'sports', 5);

-- Sub-categories under Sports (parent_id = last insert; assume id = 5)
INSERT INTO `categories` (`parent_id`, `name`, `slug`, `sort_order`) VALUES
((SELECT id FROM (SELECT id FROM categories WHERE slug = 'sports') AS t), 'Safety Gear',   'safety-gear',   1),
((SELECT id FROM (SELECT id FROM categories WHERE slug = 'sports') AS t), 'Basketball',     'basketball',     2),
((SELECT id FROM (SELECT id FROM categories WHERE slug = 'sports') AS t), 'Tennis',         'tennis',         3),
((SELECT id FROM (SELECT id FROM categories WHERE slug = 'sports') AS t), 'Soccer',         'soccer',         4),
((SELECT id FROM (SELECT id FROM categories WHERE slug = 'sports') AS t), 'Water Sports',   'water-sports',   5);

-- ---------------------------------------------------------------
-- Products — Alice Tech Store (store_id = 1) — Sports Kids
-- category_ids resolved via subquery to be safe
-- ---------------------------------------------------------------
INSERT INTO `products` (`store_id`, `category_id`, `name`, `slug`, `description`, `price`, `stock`, `status`) VALUES

(1, (SELECT id FROM categories WHERE slug = 'safety-gear'),
 'Kids Dino Helmet',
 'kids-dino-helmet',
 'Make safety fun! This lightweight, durable helmet features a cool green dinosaur graphic and integrated ventilation. The dial-fit system ensures a secure, comfortable fit for growing explorers. Age range: 4-8 Years. Head circumference: 52-56 cm. 12 ventilation vents.',
 49.50, 50, 'active'),

(1, (SELECT id FROM categories WHERE slug = 'basketball'),
 'Mini Basketball Hoop',
 'mini-basketball-hoop',
 'Perfect for indoor or outdoor play. This sturdy, portable hoop system features a durable red, white, and blue backboard, and a classic rim with an all-weather net. Age range: 6-12 Years. Wall/Door mount. Assembly required.',
 129.90, 30, 'active'),

(1, (SELECT id FROM categories WHERE slug = 'tennis'),
 'Kids Tennis Set',
 'kids-tennis-set',
 'Get ready to serve! This set includes two lightweight pink rackets, perfectly balanced for smaller hands, and a soft, low-compression tennis ball to help beginners learn without frustration. Age range: 5-9 Years. Racket length: 23 inches. Aluminum frame.',
 65.00, 40, 'active'),

(1, (SELECT id FROM categories WHERE slug = 'soccer'),
 'Kids Soccer Ball Size 3',
 'kids-soccer-ball-size-3',
 'Develop skills and have fun. This Size 3 ball features a classic stitched panel design for durability and a bright yellow and green pattern that is easy for young players to track. Age range: 4-8 Years. Stitched synthetic leather.',
 24.90, 100, 'active'),

(1, (SELECT id FROM categories WHERE slug = 'water-sports'),
 'Kids Swim Vest',
 'kids-swim-vest',
 'Build confidence in the water! This comfortable neoprene swim vest provides essential flotation assistance for young learners. Features adjustable safety straps for a secure fit. Age range: 3-6 Years. Weight range: 15-25 kg. Neoprene material.',
 58.00, 60, 'active');

-- ---------------------------------------------------------------
-- Product Images — Sports products
-- product IDs referenced by name to handle variable auto-increment
-- ---------------------------------------------------------------
INSERT INTO `product_images` (`product_id`, `image_path`, `is_primary`, `sort_order`) VALUES
((SELECT id FROM products WHERE slug = 'kids-dino-helmet'),    'uploads/products/kids-dino-helmet.webp',     1, 0),
((SELECT id FROM products WHERE slug = 'mini-basketball-hoop'),'uploads/products/mini-basketball-hoop.jpg', 1, 0),
((SELECT id FROM products WHERE slug = 'kids-tennis-set'),     'uploads/products/kids-tennis-set.webp',      1, 0),
((SELECT id FROM products WHERE slug = 'kids-soccer-ball-size-3'), 'uploads/products/kids-soccer-ball.jpg', 1, 0),
((SELECT id FROM products WHERE slug = 'kids-swim-vest'),      'uploads/products/kids-swim-vest.webp',       1, 0);

-- ---------------------------------------------------------------
-- Product Tags — Sports products
-- ---------------------------------------------------------------
INSERT INTO `product_tags` (`product_id`, `tag`) VALUES
((SELECT id FROM products WHERE slug='kids-dino-helmet'),      'kids'),
((SELECT id FROM products WHERE slug='kids-dino-helmet'),      'helmet'),
((SELECT id FROM products WHERE slug='kids-dino-helmet'),      'safety'),
((SELECT id FROM products WHERE slug='mini-basketball-hoop'),  'basketball'),
((SELECT id FROM products WHERE slug='mini-basketball-hoop'),  'hoop'),
((SELECT id FROM products WHERE slug='mini-basketball-hoop'),  'kids'),
((SELECT id FROM products WHERE slug='kids-tennis-set'),       'tennis'),
((SELECT id FROM products WHERE slug='kids-tennis-set'),       'racket'),
((SELECT id FROM products WHERE slug='kids-tennis-set'),       'kids'),
((SELECT id FROM products WHERE slug='kids-soccer-ball-size-3'),'soccer'),
((SELECT id FROM products WHERE slug='kids-soccer-ball-size-3'),'ball'),
((SELECT id FROM products WHERE slug='kids-soccer-ball-size-3'),'kids'),
((SELECT id FROM products WHERE slug='kids-swim-vest'),        'swim'),
((SELECT id FROM products WHERE slug='kids-swim-vest'),        'vest'),
((SELECT id FROM products WHERE slug='kids-swim-vest'),        'water-safety'),
(1, 'wireless'), (1, 'earbuds'),   (1, 'bluetooth'), (1, 'anc'),
(2, 'charger'),  (2, 'usb-c'),    (2, 'gan'),        (2, 'fast-charge'),
(3, 'keyboard'), (3, 'mechanical'),(3, 'rgb'),        (3, 'gaming'),
(6, 'chinos'),   (6, 'pants'),    (6, 'office'),
(7, 'sneakers'), (7, 'running'),  (7, 'women'),
(8, 'tee'),      (8, 'streetwear'),(8, 'limited'),
(10,'journal'),  (10,'stationery'),(10,'bullet-journal');

-- ---------------------------------------------------------------
-- Products — Stationery (category_id = 10) — Faber-Castell Colour Pencils
-- ---------------------------------------------------------------
INSERT INTO `products` (`store_id`, `category_id`, `name`, `slug`, `description`, `price`, `stock`, `status`) VALUES

(1, 10, 'Faber-Castell Classic Colour Pencils 12L Slim-Flexi Case',
 'fc-classic-colour-pencils-12l-slim-flexi',
 'Permanent colour pencils in a standard hexagonal shape with vivid colours and a special bonding process for break resistance. Packed in a slim, portable flexi case. Available in 12 brilliant colours. Scan, watch and learn with Faber-Castell colouring products via the QR code on the packaging.',
 16.90, 80, 'active'),

(1, 10, 'Faber-Castell Tri Colour Pencils 12L',
 'fc-tri-colour-pencils-12l',
 'Tri Colour Pencils with colour shades specially selected by art teachers. The triangular shape gives better control and comfort for children''s little fingers. Smooth leads with less breakage and minimum lead flake provide better coverage and brilliant effects on paper.',
 6.30, 100, 'active'),

(1, 10, 'Faber-Castell Tri Colour Pencils 24',
 'fc-tri-colour-pencils-24',
 'Tri Colour Pencils with 24 art teacher-selected colour shades. The triangular barrel ensures a natural grip for small hands, while smooth leads with minimum breakage deliver vivid, even colour coverage on paper.',
 12.80, 80, 'active'),

(1, 10, 'Faber-Castell Black Edition Colour Pencils 24',
 'fc-black-edition-colour-pencils-24',
 'Black Edition colour pencils with SuperSoft lead for wonderfully soft and vibrant colour laydown. High pigmentation makes them ideal for painting on light, coloured and dark paper. The ergonomic triangular shape ensures correct grip position and optimum comfort.',
 38.90, 40, 'active'),

(1, 10, 'Faber-Castell Black Edition Colour Pencils 12',
 'fc-black-edition-colour-pencils-12',
 'Black Edition colour pencils with SuperSoft lead for a soft, vibrant colour laydown. Highly pigmented leads work beautifully on light, coloured and dark paper. Ergonomic triangular barrel ensures the correct grip and comfort when drawing.',
 19.90, 60, 'active'),

(1, 10, 'Faber-Castell Grip Colour Pencils Tin of 36',
 'fc-grip-colour-pencils-tin-36',
 'The Colour Grip pencil features an ergonomic triangular barrel with a patented Soft-Grip zone for fatigue-free drawing. Highly pigmented leads deliver vivid colours. Includes 36 colours in an FSC-certified wooden barrel. Tin packaging keeps pencils organised.',
 99.00, 30, 'active'),

(1, 10, 'Faber-Castell Unicorn Edition Classic Colour Pencils 12',
 'fc-unicorn-edition-colour-pencils-12',
 'Unicorn Edition Classic Colour Pencils — a magical 12-colour set in classic hexagonal shape with vivid pigments and break-resistant bonding. Includes 10 standard colours plus 2 bonus shades. Perfect for young artists who love a touch of fantasy.',
 12.90, 60, 'active'),

(1, 10, 'Faber-Castell Dino Edition Classic Colour Pencils 12',
 'fc-dino-edition-colour-pencils-12',
 'Dino Edition Classic Colour Pencils — a 12-colour set with Faber-Castell''s classic hexagonal shape, vivid pigments and special break-resistant bonding. Includes 10 standard colours and 2 bonus shades in dino-themed packaging that kids will love.',
 13.90, 60, 'active');

-- ---------------------------------------------------------------
-- Product Images — Faber-Castell Stationery products
-- ---------------------------------------------------------------
INSERT INTO `product_images` (`product_id`, `image_path`, `is_primary`, `sort_order`) VALUES
((SELECT id FROM products WHERE slug = 'fc-classic-colour-pencils-12l-slim-flexi'),  'uploads/products/fc-classic-12l-slim-flexi.webp', 1, 0),
((SELECT id FROM products WHERE slug = 'fc-tri-colour-pencils-12l'),                 'uploads/products/fc-tri-colour-12l.webp',          1, 0),
((SELECT id FROM products WHERE slug = 'fc-tri-colour-pencils-24'),                  'uploads/products/fc-tri-colour-24l.webp',          1, 0),
((SELECT id FROM products WHERE slug = 'fc-black-edition-colour-pencils-24'),        'uploads/products/fc-black-edition-24.webp',        1, 0),
((SELECT id FROM products WHERE slug = 'fc-black-edition-colour-pencils-12'),        'uploads/products/fc-black-edition-12.webp',        1, 0),
((SELECT id FROM products WHERE slug = 'fc-grip-colour-pencils-tin-36'),             'uploads/products/fc-grip-tin-36.webp',             1, 0),
((SELECT id FROM products WHERE slug = 'fc-unicorn-edition-colour-pencils-12'),      'uploads/products/fc-unicorn-edition-12.webp',      1, 0),
((SELECT id FROM products WHERE slug = 'fc-dino-edition-colour-pencils-12'),         'uploads/products/fc-dino-edition-12.webp',         1, 0);

-- ---------------------------------------------------------------
-- Product Tags — Faber-Castell Stationery products
-- ---------------------------------------------------------------
INSERT INTO `product_tags` (`product_id`, `tag`) VALUES
((SELECT id FROM products WHERE slug='fc-classic-colour-pencils-12l-slim-flexi'),  'colour-pencils'),
((SELECT id FROM products WHERE slug='fc-classic-colour-pencils-12l-slim-flexi'),  'faber-castell'),
((SELECT id FROM products WHERE slug='fc-classic-colour-pencils-12l-slim-flexi'),  'stationery'),
((SELECT id FROM products WHERE slug='fc-tri-colour-pencils-12l'),                 'colour-pencils'),
((SELECT id FROM products WHERE slug='fc-tri-colour-pencils-12l'),                 'faber-castell'),
((SELECT id FROM products WHERE slug='fc-tri-colour-pencils-12l'),                 'kids'),
((SELECT id FROM products WHERE slug='fc-tri-colour-pencils-24'),                  'colour-pencils'),
((SELECT id FROM products WHERE slug='fc-tri-colour-pencils-24'),                  'faber-castell'),
((SELECT id FROM products WHERE slug='fc-tri-colour-pencils-24'),                  'kids'),
((SELECT id FROM products WHERE slug='fc-black-edition-colour-pencils-24'),        'colour-pencils'),
((SELECT id FROM products WHERE slug='fc-black-edition-colour-pencils-24'),        'faber-castell'),
((SELECT id FROM products WHERE slug='fc-black-edition-colour-pencils-24'),        'black-edition'),
((SELECT id FROM products WHERE slug='fc-black-edition-colour-pencils-12'),        'colour-pencils'),
((SELECT id FROM products WHERE slug='fc-black-edition-colour-pencils-12'),        'faber-castell'),
((SELECT id FROM products WHERE slug='fc-black-edition-colour-pencils-12'),        'black-edition'),
((SELECT id FROM products WHERE slug='fc-grip-colour-pencils-tin-36'),             'colour-pencils'),
((SELECT id FROM products WHERE slug='fc-grip-colour-pencils-tin-36'),             'faber-castell'),
((SELECT id FROM products WHERE slug='fc-grip-colour-pencils-tin-36'),             'grip'),
((SELECT id FROM products WHERE slug='fc-unicorn-edition-colour-pencils-12'),      'colour-pencils'),
((SELECT id FROM products WHERE slug='fc-unicorn-edition-colour-pencils-12'),      'faber-castell'),
((SELECT id FROM products WHERE slug='fc-unicorn-edition-colour-pencils-12'),      'special-edition'),
((SELECT id FROM products WHERE slug='fc-dino-edition-colour-pencils-12'),         'colour-pencils'),
((SELECT id FROM products WHERE slug='fc-dino-edition-colour-pencils-12'),         'faber-castell'),
((SELECT id FROM products WHERE slug='fc-dino-edition-colour-pencils-12'),         'special-edition');

-- ---------------------------------------------------------------
-- Visitor Tracking
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `visitor_ips` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `ip_address`   VARCHAR(45)  NOT NULL,
  `country_code` VARCHAR(2)   DEFAULT NULL,
  `country_name` VARCHAR(100) DEFAULT NULL,
  `resolved_at`  DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_visitor_ips_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `visitor_logs` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `ip_id`      INT          NOT NULL,
  `uri`        VARCHAR(500) NOT NULL DEFAULT '',
  `user_agent` VARCHAR(500) NOT NULL DEFAULT '',
  `is_bot`     TINYINT(1)   NOT NULL DEFAULT 0,
  `user_id`    INT          DEFAULT NULL,
  `created_at` DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_visitor_logs_ip_id` (`ip_id`),
  KEY `idx_visitor_logs_created_at` (`created_at`),
  CONSTRAINT `fk_visitor_logs_ip` FOREIGN KEY (`ip_id`) REFERENCES `visitor_ips` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
