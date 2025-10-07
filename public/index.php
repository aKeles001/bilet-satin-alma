<?php
session_start();
include 'header.php';
?>

<div class="text-center py-5 bg-primary text-light rounded-3 shadow-sm">
  <h1 class="display-5 fw-bold">Otobüs Biletinizi Kolayca Satın Al</h1>
  <p class="lead">Türkiye’nin her yerine güvenli ve hızlı ulaşım.</p>
</div>

<div class="card mt-5 shadow-sm">
  <div class="card-body">
    <h4 class="card-title mb-4 text-center">Sefer Ara</h4>
    <form method="GET" action="searchresults.php" class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Kalkış Noktası</label>
        <input type="text" class="form-control" name="from" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Varış Noktası</label>
        <input type="text" class="form-control" name="to" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Tarih</label>
        <input type="date" class="form-control" name="date" required>
      </div>
      <div class="col-12 text-center">
        <button type="submit" class="btn btn-primary px-5 mt-3">Seferleri Göster</button>
      </div>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>
