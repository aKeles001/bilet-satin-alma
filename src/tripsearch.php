<?php
function searchTrips($departureCity, $destinationCity, $departureDate) {
    $dbPath = '../db/database.sqlite';
    try {
        $pdo = new PDO("sqlite:" . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "SELECT t.id, t.departure_city, t.destination_city, t.departure_time, t.arrival_time, t.price, t.capacity, b.name AS company_name
                  FROM Trips t
                  JOIN Bus_Company b ON t.company_id = b.id
                  WHERE t.departure_city LIKE :departure_city
                    AND t.destination_city LIKE :destination_city
                    AND DATE(t.departure_time) = DATE(:departure_date)
                  ORDER BY t.departure_time ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':departure_city' => "%$departureCity%",
            ':destination_city' => "%$destinationCity%",
            ':departure_date' => $departureDate
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        return ['error' => $e->getMessage()];
    }
}
?>
