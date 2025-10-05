<?php

$dbPath = __DIR__ . '/db/database.sqlite';
$schemaFile = __DIR__ . '/db/schema.sql';

// UUID Generator
function uuid()
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}


try {
    // Delete old DB for a clean initialization
    if (file_exists($dbPath)) {
        unlink($dbPath);
    }

    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Enable foreign keys
    $db->exec("PRAGMA foreign_keys = ON;");

    // Check if schema.sql exists
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    $schema = file_get_contents($schemaFile);
    $db->exec($schema);
    echo "Schema imported from schema.sql.\n";

    // SAMPLE DATA
    
    $companyId = uuid();
    $adminId = uuid();
    $firmAdminId = uuid();
    $userId = uuid();
    $tripId = uuid();

    // bus company
    $stmt = $db->prepare("INSERT INTO Bus_Company (id, name, logo_path) VALUES (?, ?, ?)");
    $stmt->execute([$companyId, 'Kamil Koç', '/images/kamilkoc.png']);

    // users
    $stmt = $db->prepare("INSERT INTO User (id, full_name, email, role, password, company_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$adminId, 'Admin User', 'admin@bilet.com', 'admin', password_hash('admin123', PASSWORD_DEFAULT), null]);
    $stmt->execute([$firmAdminId, 'Firma Yetkilisi', 'firma@kamilkoc.com', 'company', password_hash('firma123', PASSWORD_DEFAULT), $companyId]);
    $stmt->execute([$userId, 'Ahmet Keleş', 'user@bilet.com', 'user', password_hash('user123', PASSWORD_DEFAULT), null]);

    // trip
    $stmt = $db->prepare("INSERT INTO Trips (id, company_id, destination_city, departure_city, departure_time, arrival_time, price, capacity)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $tripId, $companyId, 'Istanbul', 'Ankara',
        '2025-10-12 09:00:00', '2025-10-12 14:00:00', 350, 45
    ]);

    echo "Sample data inserted successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
