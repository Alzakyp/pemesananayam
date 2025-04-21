<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />

    <title>Dashboard | Toko Supplier Daging Ayam Segar</title>

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
    <link href="{{ asset('assets/style/main.css') }}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="../assets/vendor/DataTables/datatables.min.css" />
    <style>
        .dropdown-toggle:focus {
            outline-style: none;
        }

        /* style.css */

        Gaya Umum body {
            font-family: 'Arial', sans-serif;
            /* Menggunakan font Arial */
            background-color: #f8f9fa;
            /* Latar belakang abu-abu terang */
        }

        /* Judul Dashboard */
        .dashboard-title {
            font-size: 1.5rem;
            /* Ukuran font untuk judul */
            color: #343a40;
            /* Warna teks judul */
        }

        /* Subtitle Dashboard */
        .dashboard-subtitle {
            font-size: 1rem;
            /* Ukuran font untuk subtitle */
            color: #6c757d;
            /* Warna teks subtitle */
        }

        /* Gaya untuk Form Group */
        .form-group label {
            font-weight: bold;
            /* Menebalkan label */
            color: #495057;
            /* Warna teks label */
        }

        /* Gaya untuk Input dan Select */
        .form-control {
            border: 1px solid #ced4da;
            /* Border abu-abu */
            border-radius: 0.25rem;
            /* Radius sudut */
            padding: 0.375rem 0.75rem;
            /* Padding dalam input */
            font-size: 1rem;
            /* Ukuran font input */
        }

        /* Gaya untuk Tombol */
        .btn-success {
            background-color: #28a745;
            /* Warna hijau untuk tombol */
            border-color: #28a745;
            /* Border hijau */
            font-weight: bold;
            /* Menebalkan teks tombol */
        }

        /* Hover efek pada tombol */
        .btn-success:hover {
            background-color: #218838;
            /* Warna hijau gelap saat hover */
            border-color: #1e7e34;
            /* Border hijau gelap saat hover */
        }

        /* Gambar Produk */
        .img-fluid {
            max-width: 100%;
            /* Gambar responsif */
            height: auto;
            /* Tinggi otomatis untuk mempertahankan rasio */


        }
    </style>

</head>

<body>
    <div id="app">
        <div class="page-dashboard">
            <div class="d-flex" id="wrapper" data-aos="fade-right">
                <!-- sidebar -->
                <div class="border-right" id="sidebar-wrapper">
                    <div class="sidebar-heading text-center">
                        <!-- <img src="../assets/images/logosidebarku.png" alt="" class="my-2 w-50" /> -->
                        <img src="../assets/images/LOGO AYAMKU.png" alt="" class="my-2"
                            style="width: 150px; height: 149px;" />

                    </div>
                    <div class="list-group list-group-flush">
                        <a href="/dashboard"
                            class="list-group-item list-group-item-action {{ Request::is('dashboard') ? 'active' : '' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('produk.index') }}"
                            class="list-group-item list-group-item-action {{ Request::is('produk*') ? 'active' : '' }}">
                            Produk
                        </a>
                        <a href="{{ route('pesanan.index') }}"
                            class="list-group-item list-group-item-action {{ Request::is('pesanan*') ? 'active' : '' }}">
                            Pesanan
                        </a>
                        <a href="{{ route('pembayaran.index') }}"
                            class="list-group-item list-group-item-action {{ Request::is('pembayaran*') ? 'active' : '' }}">
                            <i class="fas fa-money-bill-wave mr-2"></i> Transaksi
                        </a>
                        <a href="{{ route('user.index') }}"
                            class="list-group-item list-group-item-action {{ Request::is('users') ? 'active' : '' }}">
                            Data Pengguna
                        </a>
                        <!-- <a href="?page=logout" class="list-group-item list-group-item-action">
                            Sign Out
                        </a> -->
                    </div>
                </div>

                <!-- page content -->
                <div id="page-content-wrapper">
                    <!-- Your navbar and main content here -->
                    <nav class="navbar navbar-expand-lg navbar-light navbar-store fixed-top" data-aos="fade-down">
                        <div class="container-fluid">
                            <button class="btn btn-secondary d-md-none mr-auto mr-2" id="menu-toggle">
                                &laquo; Menu
                            </button>
                            <button class="navbar-toggler" type="button" data-toggle="collapse"
                                data-target="#navbarResponsive">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse" id="navbarResponsive">
                                <ul class="navbar-nav d-none d-lg-flex ml-auto">
                                    <li class="nav-item dropdown">
                                        <a href="#" class="nav-link" id="navbarDropdown" role="button"
                                            data-toggle="dropdown">
                                            <img src="{{ asset('assets/images/person-circle.svg') }}" alt="profile"
                                                height="40px" class="rounded-circle mr-2 profile-picture" />
                                            Hi, {{ Auth::user()->nama }}
                                        </a>
                                        <div class="dropdown-menu">
                                            <form action="{{ route('logout') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">Logout</button>
                                            </form>
                                        </div>
                                    </li>
                                </ul>
                                <ul class="navbar-nav d-block d-lg-none">
                                    <li class="nav-item">
                                        <a href="" class="nav-link">Hi, Yeti Apriliana Dewi</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </nav>

                    <div class="main-content">
                        <section class="section">
                            @yield('content')
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript -->
    <script src="../assets/vendor/jquery/jquery.slim.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
    <script>
        $("#menu-toggle").click(function(e) {
            e.preventDefault();
            $("#wrapper").toggleClass("toggled");
        });
    </script>
    <script type="text/javascript" src="{{ asset('assets/vendor/DataTables/datatables.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#table').DataTable();
        });
    </script>
</body>

</html>
