# bilet-satin-alma

Bu proje, PHP ve SQLite tabanlı çok kullanıcılı bir otobüs bileti satın alma platformudur. Kullanıcıların sefer aramasına, koltuk seçmesine ve bilet satın almasına olanak tanır.

## Özellikler

- **Çoklu Kullanıcı Rolleri:**
  - **Yönetici (Admin):** Sisteme yeni otobüs firmaları ve firma yetkilileri ekleyebilir.
  - **Firma Yetkilisi (Company):** Kendi firmasına ait seferleri (kalkış, varış, saat, fiyat vb.) yönetebilir.
  - **Kullanıcı (User):** Seferleri arayabilir, koltuk seçebilir ve bilet satın alabilir.
- **Sefer Arama:** Kullanıcılar kalkış ve varış noktasına ve tarihe göre sefer arayabilir.
- **Koltuk Seçimi:** Kullanıcılar otobüs planı üzerinden görsel olarak koltuk seçimi yapabilir.
- **Kupon Sistemi:** İndirim kuponları ile daha ucuza bilet alma imkanı.

## Kullanılan Teknolojiler

- **Backend:** PHP
- **Veritabanı:** SQLite
- **Frontend:** HTML, CSS, JavaScript, Bootstrap 5
- **Containerization:** Docker

## Kurulum ve Çalıştırma

Projeyi yerel makinenizde çalıştırmak için Docker kullanabilirsiniz.

1.  **Projeyi Klonlayın:**

    ```bash
    git clone <proje-repo-adresi>
    cd bilet-satin-alma
    ```

2.  **Docker Container'ını Başlatın:**
    Proje ana dizinindeyken aşağıdaki komutu çalıştırın. Docker ayağa kaldırıldığında veri tabanı oluşturulacaktır:,

    ```bash
    docker-compose up -d --build
    ```

3.  **Veritabanını Yeniden Kurma:**
    Veritabanını temiz bir şekilde tekrar kurma için aşağıdaki komutu çalıştırın.

    ```bash
    php init_db.php
    ```

    Bu komut, `db/` dizininde `database.sqlite` dosyasını oluşturacak ve `schema.sql` dosyasındaki tablo yapısını kuracaktır.

4.  **Uygulamaya Erişin:**
    Kurulum tamamlandıktan sonra, web tarayıcınızdan `http://localhost:8080` adresine giderek uygulamayı görüntüleyebilirsiniz.

## örnek Kullanıcı Bilgileri

- **Admin:** `admin@bilet.com` - `admin123`
- **Firma:** `firma@kamilkoc.com` - `firma123`
- **Kullanıcı:** `user@bilet.com` - `user123`
