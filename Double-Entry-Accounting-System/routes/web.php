<?php

use App\Helpers\SMSHelperElite;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

Auth::routes();

require base_path('/routes/admin/home.php');
require base_path('/routes/admin/store.php');
require base_path('/routes/admin/brand.php');
require base_path('/routes/admin/category.php');
require base_path('/routes/admin/unit.php');
require base_path('/routes/admin/product.php');
require base_path('/routes/admin/tax.php');
require base_path('/routes/admin/additional-field.php');
require base_path('/routes/admin/permissions.php');
require base_path('/routes/admin/roles.php');
require base_path('/routes/admin/users.php');
require base_path('/routes/admin/profile.php');
require base_path('/routes/admin/suppliers.php');
require base_path('/routes/admin/customers.php');
require base_path('/routes/admin/purchase.php');
require base_path('/routes/admin/sale.php');
require base_path('/routes/admin/stock-adjustment.php');
require base_path('/routes/admin/password-otp.php');
require base_path('/routes/admin/report.php');
require base_path('/routes/admin/company.php');
require base_path('/routes/admin/settings.php');
require base_path('/routes/admin/activity-log.php');
require base_path('/routes/admin/extra.php');
