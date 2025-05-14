<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "article_management");

if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

// Setel tanggal default jika tidak ada parameter 'start' dan 'end'
if (!isset($_GET['start']) || !isset($_GET['end'])) {
    // Jika tidak ada parameter tanggal, tampilkan semua artikel
    $_GET['start'] = '1970-01-01'; // Set tanggal mulai sangat awal
    $_GET['end'] = date('Y-m-d'); // Tanggal hari ini
}

// Ambil filter tanggal (jika ada)
$dateFilter = "";
if (isset($_GET['start']) && isset($_GET['end'])) {
    $startDate = $_GET['start'] . ' 00:00:00'; 
    $endDate = $_GET['end'] . ' 23:59:59';
    $dateFilter = "WHERE a.created BETWEEN '$startDate' AND '$endDate'";
}

// Total artikel dengan filter tanggal (jika ada)
$totalArticlesQuery = "SELECT COUNT(*) as total FROM articles a $dateFilter";
$totalArticles = $conn->query($totalArticlesQuery)->fetch_assoc()['total'];

// Artikel berdasarkan status dengan filter tanggal (jika ada)
$statusCounts = $conn->query("SELECT status, COUNT(*) as jumlah FROM articles a $dateFilter GROUP BY status");

$statusData = [];
while ($row = $statusCounts->fetch_assoc()) {
    $statusData[$row['status']] = $row['jumlah'];
}

// Artikel publish per bulan (6 bulan terakhir)
$publishedPerMonth = $conn->query("
    SELECT DATE_FORMAT(a.created, '%Y-%m') as bulan, COUNT(*) as jumlah 
    FROM articles a 
    $dateFilter
    AND a.status = 'published' 
    GROUP BY bulan 
    ORDER BY bulan ASC
");

$bulan = $jumlahPublikasi = [];
while ($row = $publishedPerMonth->fetch_assoc()) {
    $bulan[] = $row['bulan'];
    $jumlahPublikasi[] = $row['jumlah'];
}

// Top 5 author berdasarkan jumlah artikel
$topAuthorArticle = $conn->query("SELECT u.username, COUNT(*) as jumlah FROM articles a JOIN users u ON a.author = u.user_id GROUP BY a.author ORDER BY jumlah DESC LIMIT 5");

// Top 5 author berdasarkan jumlah view
$topAuthorView = $conn->query("SELECT u.username, SUM(a.views) as total_views FROM articles a JOIN users u ON a.author = u.user_id GROUP BY a.author ORDER BY total_views DESC LIMIT 5");

// Tren kontribusi author (jumlah artikel per bulan)
$authorTrend = $conn->query("SELECT u.username, DATE_FORMAT(a.created, '%Y-%m') as bulan, COUNT(*) as jumlah 
                             FROM articles a 
                             JOIN users u ON a.author = u.user_id 
                             $dateFilter
                             GROUP BY a.author, bulan 
                             ORDER BY bulan ASC");
$trendData = [];
while ($row = $authorTrend->fetch_assoc()) {
    $trendData[$row['username']][$row['bulan']] = $row['jumlah'];
}

// Artikel per kategori
$categoryData = $conn->query("SELECT c.category_name, COUNT(*) as jumlah FROM articles a JOIN categories c ON a.category_id = c.category_id GROUP BY a.category_id");

// Rata-rata komentar per artikel
$avgComment = $conn->query("SELECT ROUND(COUNT(*) / (SELECT COUNT(*) FROM articles), 2) as avg FROM comments")->fetch_assoc()['avg'];

// Top 5 artikel dengan komentar terbanyak
$topComments = $conn->query("SELECT title, (SELECT COUNT(*) FROM comments c WHERE c.article_id = a.article_id) as comment_count 
                             FROM articles a ORDER BY comment_count DESC LIMIT 5");

// Top 5 artikel dengan view terbanyak
$topViews = $conn->query("SELECT title, views FROM articles ORDER BY views DESC LIMIT 5");

// Pagination setup
$limit = 5;  // Set limit per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query untuk mengambil artikel lengkap dengan views dan author, serta pagination
$articlesQuery = "SELECT a.article_id, a.title, a.views, u.username 
                  FROM articles a 
                  JOIN users u ON a.author = u.user_id
                  $dateFilter
                  LIMIT $limit OFFSET $offset";

$articles = $conn->query($articlesQuery);

// Total jumlah artikel untuk pagination (dengan atau tanpa filter tanggal)
$totalArticlesQueryWithFilter = "SELECT COUNT(*) as total FROM articles a $dateFilter";
$totalArticlesFiltered = $conn->query($totalArticlesQueryWithFilter)->fetch_assoc()['total'];
$totalPages = ceil($totalArticlesFiltered / $limit);

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Manajemen Konten</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        canvas {
            height: 300px !important;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Dashboard Manajemen Konten</h2>

    <!-- Filter Tanggal -->
    <form class="mb-4" method="get">
        <div class="row">
            <div class="col-md-4">
                <label for="startDate" class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" id="startDate" name="start" value="<?= $startDate ?>">
            </div>
            <div class="col-md-4">
                <label for="endDate" class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" id="endDate" name="end" value="<?= $endDate ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary mt-4">Terapkan Filter</button>
            </div>
        </div>
    </form>


    <!-- Card Performa Artikel -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Artikel</h5>
                    <p class="card-text fs-4"><?= $totalArticles ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Artikel Published</h5>
                    <p class="card-text fs-4"><?= $statusData['published'] ?? 0 ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <h5 class="card-title">Artikel Draft</h5>
                    <p class="card-text fs-4"><?= $statusData['draft'] ?? 0 ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Row -->
    <div class="row mb-4">
        <div class="col-md-12">
            <canvas id="barChart"></canvas>
        </div>
    </div>

    <!-- Panel Aktifitas Author -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h5>Top 5 Author berdasarkan Jumlah Artikel</h5>
            <ul class="list-group">
                <?php while ($row = $topAuthorArticle->fetch_assoc()): ?>
                    <li class="list-group-item"><?= $row['username'] ?> - <?= $row['jumlah'] ?> artikel</li>
                <?php endwhile; ?>
            </ul>
        </div>

        <div class="col-md-6">
            <h5>Top 5 Author berdasarkan Jumlah View</h5>
            <ul class="list-group">
                <?php while ($row = $topAuthorView->fetch_assoc()): ?>
                    <li class="list-group-item"><?= $row['username'] ?> - <?= $row['total_views'] ?> views</li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <!-- Tren kontribusi Author -->
    <div class="row mb-4">
        <div class="col-md-12">
            <canvas id="lineChart"></canvas>
        </div>
    </div>

    <!-- Komponen analisa konten -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h5>Artikel per Kategori</h5>
            <canvas id="pieChart"></canvas>
        </div>

        <div class="col-md-6">
            <h5>Rata-rata Komentar per Artikel</h5>
            <p><?= $avgComment ?> komentar</p>
        </div>
    </div>

    <!-- Top 5 Artikel dengan Komentar Terbanyak -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h5>Top 5 Artikel dengan Komentar Terbanyak</h5>
            <ul class="list-group">
                <?php while ($row = $topComments->fetch_assoc()): ?>
                    <li class="list-group-item"><?= $row['title'] ?> - <?= $row['comment_count'] ?> komentar</li>
                <?php endwhile; ?>
            </ul>
        </div>

        <!-- Top 5 Artikel dengan View Terbanyak -->
        <div class="col-md-6">
            <h5>Top 5 Artikel dengan View Terbanyak</h5>
            <ul class="list-group">
                <?php while ($row = $topViews->fetch_assoc()): ?>
                    <li class="list-group-item"><?= $row['title'] ?> - <?= $row['views'] ?> views</li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <!-- Tabel Artikel -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h5>Daftar Artikel</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Judul Artikel</th>
                        <th>Views</th>
                        <th>Author</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $offset + 1;
                    while ($row = $articles->fetch_assoc()) {
                        echo "<tr>
                                <td>{$no}</td>
                                <td>{$row['title']}</td>
                                <td>{$row['views']}</td>
                                <td>{$row['username']}</td>
                            </tr>";
                        $no++;
                    }
                    ?>
                </tbody>
            </table>

            <!-- Pagination Links -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=1">First</a></li>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Prev</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
                        <li class="page-item"><a class="page-link" href="?page=<?= $totalPages ?>">Last</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
    // Bar Chart: Artikel published 6 bulan terakhir
    const barChart = new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($bulan) ?>,
            datasets: [{
                label: 'Artikel Published',
                data: <?= json_encode($jumlahPublikasi) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Line Chart: Tren kontribusi author per bulan
    const lineChart = new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($bulan) ?>,
            datasets: [
                <?php foreach ($trendData as $author => $data): ?>
                {
                    label: <?= json_encode($author) ?>,
                    data: [
                        <?php
                        foreach ($bulan as $b) {
                            echo isset($data[$b]) ? $data[$b] : 0;
                            echo ",";
                        }
                        ?>
                    ],
                    borderColor: '<?= sprintf("rgba(%d, %d, %d, 1)", rand(0,255), rand(0,255), rand(0,255)) ?>',
                    fill: false
                },
                <?php endforeach; ?>
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Pie Chart: Artikel per kategori
    const pieChart = new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: <?php
                $kategoriLabels = [];
                $kategoriData = [];
                while ($row = $categoryData->fetch_assoc()) {
                    $kategoriLabels[] = $row['category_name'];
                    $kategoriData[] = $row['jumlah'];
                }
                echo json_encode($kategoriLabels);
            ?>,
            datasets: [{
                data: <?= json_encode($kategoriData) ?>,
                backgroundColor: [
                    <?php
                        $colors = [];
                        foreach ($kategoriLabels as $i => $label) {
                            $colors[] = "'rgba(" . rand(0,255) . "," . rand(0,255) . "," . rand(0,255) . ", 0.7)'";
                        }
                        echo implode(", ", $colors);
                    ?>
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>
</body>
</html>
