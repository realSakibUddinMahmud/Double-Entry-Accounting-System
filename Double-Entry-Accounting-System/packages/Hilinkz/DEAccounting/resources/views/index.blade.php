@extends('admin.layouts.new_admin')

@section('impersonate_leave')
    @include('admin.layouts.impersonate-leave')
@endsection

@section('custom_style')
    @include('styles.data-table')
    @include('styles.general')
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Double Entry Accounting Package/Module</h1>
                        <p>A complete package for any business accounting. Developed by HiLinkz</p>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->


        <!-- Example of using AdminLTE components -->
        <div class="content">
            <div class="container">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Documentation</h3>
                    </div>
                    <div class="card-body">
                        <p>details write here..</p>
                        <p>Update you main app composer.json
                            "repositories": [
                            {
                            "type": "path",
                            "url": "packages/Hilinkz/DEAccounting"
                            }
                            ],</p>
                        <p>To install the package: composer require hilinkz/de-accounting:dev-master</p>
                        <p>change .env.example to .env</p>
                        <p>composer install</p>
                        <p>Create a local DB</p>
                        <p>php artisan key:generate</p>
                        <p>php artisan migrate</p>
                        <p>composer require barryvdh/laravel-debugbar --dev</p>
                        <p>php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
