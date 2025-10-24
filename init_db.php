<?php

$dbPath = __DIR__ . '/db/database.sqlite';
$schemaFile = __DIR__ . '/db/schema.sql';


if (file_exists($dbPath)) {
    $db = null;
    sleep(1); 
    unlink($dbPath);
    echo "Existing database deleted.\n";
}

include_once __DIR__ . '/src/db_connect.php';
include_once __DIR__ . '/src/helper.php';

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("PRAGMA foreign_keys = ON;");

    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    $schema = file_get_contents($schemaFile);
    $db->exec($schema);
    echo "Schema imported from schema.sql.\n";

    // SAMPLE DATA
    
    $companyId1 = uuid();
    $companyId2 = uuid();
    $adminId = uuid();
    $firmAdminId1 = uuid();
    $firmAdminId2 = uuid();
    $userId = uuid();
    $tripId1 = uuid();
    $tripId2 = uuid();
    $tripId3 = uuid();
    $tripId4 = uuid();

    // bus company
    $stmt = $db->prepare("INSERT INTO Bus_Company (id, name, logo_path) VALUES (?, ?, ?)");
    $stmt->execute([$companyId1, 'Kamil Koç', '/images/company_logos/kamilkoc.png']);
    $stmt->execute([$companyId2, 'Kontur', '/images/company_logos/kontur.png']);

    // users
    $stmt = $db->prepare("INSERT INTO User (id, full_name, email, role, password, company_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$adminId, 'Admin User', 'admin@bilet.com', 'admin', password_hash('admin123', PASSWORD_DEFAULT), null]);
    $stmt->execute([$firmAdminId1, 'Firma Yetkilisi', 'firma@kamilkoc.com', 'company', password_hash('firma123', PASSWORD_DEFAULT), $companyId1]);
    $stmt->execute([$firmAdminId2, 'Firma Yetkilisi', 'firma@kontur.com', 'company', password_hash('firma123', PASSWORD_DEFAULT), $companyId2]);
    $stmt->execute([$userId, 'User', 'user@bilet.com', 'user', password_hash('user123', PASSWORD_DEFAULT), null]);

    // trip
    $stmt = $db->prepare("INSERT INTO Trips (id, company_id, destination_city, departure_city, departure_time, arrival_time, price, capacity)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $tripId1, $companyId1, 'Istanbul', 'Ankara',
        '2025-11-10 09:00:00', '2025-11-10 14:00:00', 150, 45
    ]);
    $stmt->execute([
    $tripId2, $companyId2, 'Izmir', 'Ankara',
    '2025-12-15 08:00:00', '2025-12-15 13:00:00', 300, 50
    ]);

    $stmt->execute([
        $tripId3, $companyId1, 'Bursa', 'Istanbul',
        '2026-01-10 10:00:00', '2026-01-10 12:30:00', 180, 40
    ]);

    $stmt->execute([
        $tripId4, $companyId2, 'Antalya', 'Istanbul',
        '2025-10-15 07:30:00', '2025-10-15 14:00:00', 450, 55
    ]);
    //Coupon
    $stmt = $db->prepare("INSERT INTO Coupons (id, code, discount, company_id, usage_limit, expire_date)
                            VALUES (?, ?, ?, ?, ?, ?)"); 
    $stmt->execute([
        uuid(), 'IND', 50, $companyId1,  10, 2026-10-15]);
    $stmt->execute([
        uuid(), 'IND20', 20, $companyId2, 10, 2026-10-15]);

    $stmt->execute([
        uuid(), 'ADM', 100, NULL, 10, 2026-10-15]);
    echo "Sample data inserted successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
