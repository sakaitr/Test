<?php
$current = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fatura Takip Sistemi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/index.php">
            <i class="bi bi-receipt-cutoff"></i> Fatura Takip
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'index' ? 'active' : '' ?>" href="/index.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'firmalar' ? 'active' : '' ?>" href="/firmalar.php">
                        <i class="bi bi-building"></i> Firmalar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'musteriler' ? 'active' : '' ?>" href="/musteriler.php">
                        <i class="bi bi-people"></i> Müşteriler
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'sevkiyatlar' ? 'active' : '' ?>" href="/sevkiyatlar.php">
                        <i class="bi bi-truck"></i> Sevkiyatlar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'faturalar' ? 'active' : '' ?>" href="/faturalar.php">
                        <i class="bi bi-file-earmark-text"></i> Faturalar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'musteri_hareketler' ? 'active' : '' ?>" href="/musteri_hareketler.php">
                        <i class="bi bi-arrow-left-right"></i> Hareketler
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid py-4">
