@extends('layouts.app')

@section('title', 'Data Produk')

@section('content')
<div
  class="section-content section-dashboard-home"
  data-aos="fade-up"
>
  <div class="container-fluid">
    <div class="dashboard-heading">
      <h2 class="dashboard-title">Dashboard</h2>
      <p class="dashboard-subtitle">Laporan Penjualan Dan Pendapatan</p> |   <a href='' title='' target='_blank'>UD.AYAM POTONG RIZKY</a>
      
    </div>
    <div class="dashboard-content">
      <div class="row">
        <div class="col-md-4">
          <div class="card mb-2">
            <div class="card-body">
                          <div class="dashboard-card-title">Pelanggan</div>
              <div class="dashboard-card-subtitle">7</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card mb-2">
            <div class="card-body">
                          <div class="dashboard-card-title">Pendapatan</div>
              <div class="dashboard-card-subtitle">Rp. 208.000</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card mb-2">
            <div class="card-body">
                          <div class="dashboard-card-title">Transaksi</div>
              <div class="dashboard-card-subtitle">3</div>
            </div>
          </div>
        </div>
      </div>
@endsection