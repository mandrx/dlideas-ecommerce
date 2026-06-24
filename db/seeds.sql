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
(3, 'uploads/products/keyboard-rgb-main.jpg', 1, 0),
(3, 'uploads/products/keyboard-rgb-side.jpg', 0, 1),
-- Chinos (product 6)
(6, 'uploads/products/chinos-khaki-main.jpg', 1, 0),
(6, 'uploads/products/chinos-navy.jpg',       0, 1),
-- Sneakers (product 7)
(7, 'uploads/products/sneakers-main.jpg',     1, 0),
-- Graphic Tee (product 8)
(8, 'uploads/products/tee-front.jpg',         1, 0),
(8, 'uploads/products/tee-back.jpg',          0, 1),
-- Bullet Journal (product 10)
(10,'uploads/products/journal-main.jpg',      1, 0);

-- ---------------------------------------------------------------
-- Product Tags
-- ---------------------------------------------------------------
INSERT INTO `product_tags` (`product_id`, `tag`) VALUES
(1, 'wireless'), (1, 'earbuds'),   (1, 'bluetooth'), (1, 'anc'),
(2, 'charger'),  (2, 'usb-c'),    (2, 'gan'),        (2, 'fast-charge'),
(3, 'keyboard'), (3, 'mechanical'),(3, 'rgb'),        (3, 'gaming'),
(6, 'chinos'),   (6, 'pants'),    (6, 'office'),
(7, 'sneakers'), (7, 'running'),  (7, 'women'),
(8, 'tee'),      (8, 'streetwear'),(8, 'limited'),
(10,'journal'),  (10,'stationery'),(10,'bullet-journal');
