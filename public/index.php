<?php
session_start();
include 'header.php';
?>


<div class="main-content">
  <div class="text-center py-5 bg-dark text-light shadow-sm">
    <h1 class="display-5 fw-bold">Otobüs Biletinizi Kolayca Satın Al</h1>
    <p class="lead">Türkiye’nin her yerine güvenli ve hızlı ulaşım.</p>
  </div>

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4">
          <form action="searchresults.php" method="GET">
            <div class="row g-3 align-items-end">

              <!-- Departure Input -->
              <div class="col-md">
                <label for="fromInput" class="form-label fw-bold">Kalkış</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                  <input type="text" class="form-control" id="fromInput" name="from" placeholder="Nereden?" required>
                </div>
              </div>

              <!-- Arrival Input -->
              <div class="col-md">
                <label for="toInput" class="form-label fw-bold">Varış</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                  <input type="text" class="form-control" id="toInput" name="to" placeholder="Nereye?" required>
                </div>
              </div>

              <!-- Date Input -->
              <div class="col-md">
                <label for="dateInput" class="form-label fw-bold">Tarih</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                  <input type="date" class="form-control" id="dateInput" name="date" required>
                </div>
              </div>

              <!-- Submit Button -->
              <div class="col-md-auto">
                <button class="btn btn-dark btn-lg w-100" type="submit">
                  <i class="bi bi-search me-1"></i> Ara
                </button>
              </div>
            </div>
          </form>
        </div>
    </div>
</div>


<?php include 'footer.php'; ?>
