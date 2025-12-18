<?php
require_once __DIR__ . '/database.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            phone VARCHAR(20),
            address TEXT,
            is_verified BOOLEAN DEFAULT FALSE,
            verification_token VARCHAR(255),
            role VARCHAR(20) DEFAULT 'customer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS menu_items (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50),
    image_url VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


        CREATE TABLE IF NOT EXISTS orders (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id),
            total_amount DECIMAL(10, 2) NOT NULL,
            shipping_fee DECIMAL(10, 2) DEFAULT 0,
            delivery_address TEXT NOT NULL,
            status VARCHAR(30) DEFAULT 'pending',
            payment_method VARCHAR(50),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS order_items (
            id SERIAL PRIMARY KEY,
            order_id INTEGER REFERENCES orders(id),
            menu_item_id INTEGER REFERENCES menu_items(id),
            quantity INTEGER NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS cart (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id),
            menu_item_id INTEGER REFERENCES menu_items(id),
            quantity INTEGER DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, menu_item_id)
        );

        CREATE TABLE IF NOT EXISTS delivery_areas (
            id SERIAL PRIMARY KEY,
            area_name VARCHAR(100) NOT NULL,
            shipping_fee DECIMAL(10, 2) NOT NULL,
            estimated_time VARCHAR(50)
        );
    ");

    $stmt = $pdo->query("SELECT COUNT(*) FROM menu_items");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // Updated image_url values to .jpg filenames
        $pdINSERT "INSERT INTO menu_items (name, description, price, category, image_url, is_available) VALUES 
            ('Isaw', 'Grilled chicken intestines on a stick, served with spicy vinegar', 10.00, 'Grilled', 'assets/img/isaw.jpg', 1),
            ('Fishball', 'Deep-fried fish balls served with sweet or spicy sauce', 20.00, 'Fried', 'assets/img/fishball.jpg', 1),
            ('Kwek-Kwek', 'Deep-fried quail eggs coated in orange batter', 25.00, 'Fried', 'assets/img/kwekkwek.jpg', 1),
            ('Balut', 'Fertilized duck egg, a delicacy boiled and eaten from the shell', 35.00, 'Exotic', 'assets/img/balut.jpg', 1),
            ('Banana Cue', 'Caramelized fried bananas on a stick', 15.00, 'Sweets', 'assets/img/bananacue.jpg', 1),
            ('Turon', 'Fried banana spring roll with jackfruit', 20.00, 'Sweets', 'assets/img/turon.jpg', 1),
            ('Betamax', 'Grilled chicken blood cubes', 15.00, 'Grilled', 'assets/img/betamax.jpg', 1),
            ('Adidas', 'Grilled chicken feet, crispy and flavorful', 20.00, 'Grilled', 'assets/img/adidas.jpg', 1),
            ('Proben', 'Deep-fried chicken proventriculus (gizzard skin)', 20.00, 'Fried', 'assets/img/proben.jpg', 1),
            ('Halo-Halo', 'Mixed crushed ice, milk, and various sweet beans/fruits', 85.00, 'Sweets', 'assets/img/halohalo.jpg', 1)";
        
        $pdo->exec("
            INSERT INTO delivery_areas (area_name, shipping_fee, estimated_time) VALUES
            ('CENTRAL 2', 50.00, '30-45 mins'),
            ('LIMATOK', 60.00, '45-60 mins'),
            ('MATIAO', 55.00, '35-50 mins'),
            ('MINZE', 65.00, '45-60 mins'),
            ('SAINZ', 70.00, '50-65 mins'),
            ('DAHICAN', 55.00, '35-50 mins'),
            ('MARTNEZ', 75.00, '55-70 mins'),
            ('URBAN', 80.00, '60-75 mins'),
            ('DMM', 85.00, '65-80 mins'),
            ('BUSO', 70.00, '50-65 mins');
        ");

        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, is_verified, role) VALUES ('admin', 'admin@streetgo.ph', ?, 'StreetGo Admin', TRUE, 'admin') ON CONFLICT (email) DO NOTHING");
        $stmt->execute([$adminPassword]);
    }

    echo "Database initialized successfully!";
} catch (PDOException $e) {
    die("Database initialization failed: " . $e->getMessage());
}
?>