<?php
session_start();
include 'header.php';
include '../src/tripsearch.php';

// Get user input from GET request
$departureCity = $_GET['from'] ?? '';
$destinationCity = $_GET['to'] ?? '';
$departureDate = $_GET['date'] ?? '';

$trips = searchTrips($departureCity, $destinationCity, $departureDate);
?>

<div class="main-content container mt-5">
    <h3 class="mb-4 text-center">Sefer Sonuçları</h3>

    <?php if (isset($trips['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($trips['error']) ?></div>
    <?php elseif (empty($trips)): ?>
        <div class="alert alert-warning text-center">Maalesef, aradığınız kriterlere uygun sefer bulunamadı.</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <?php foreach ($trips as $trip): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($trip['departure_city']) ?> → <?= htmlspecialchars($trip['destination_city']) ?></h5>
                            <p class="card-text">
                                <strong>Firma:</strong> <?= htmlspecialchars($trip['company_name']) ?><br>
                                <strong>Kalkış:</strong> <?= date('d-m-Y H:i', strtotime($trip['departure_time'])) ?><br>
                                <strong>Varış:</strong> <?= date('d-m-Y H:i', strtotime($trip['arrival_time'])) ?><br>
                                <strong>Fiyat:</strong> ₺<?= number_format($trip['price'], 2) ?><br>
                                <strong>Kapasite:</strong> <?= $trip['capacity'] ?>
                            </p>
                            <a href="book_ticket.php?trip_id=<?= $trip['id'] ?>" class="btn btn-primary">Bilet Satın Al</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
