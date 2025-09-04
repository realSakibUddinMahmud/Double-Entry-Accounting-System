<?php

namespace Hilinkz\DEAccounting;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class DEAccountingServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register services
    }

    public function boot()
    {
        if (!$this->app->bound('files')) {
            $this->app->singleton('files', function ($app) {
                return new \Illuminate\Filesystem\Filesystem();
            });
        }

        // Load migrations, routes, etc.
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        // Load Routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        Livewire::component('de-accounting::create-account', \Hilinkz\DEAccounting\Http\Livewire\AccountComponent::class);
        Livewire::component('de-accounting::edit-account', \Hilinkz\DEAccounting\Http\Livewire\AccountComponent::class);

        Livewire::component('de-accounting::new-fund-transfer', \Hilinkz\DEAccounting\Http\Livewire\FundTransferComponent::class);
        Livewire::component('de-accounting::new-payment', \Hilinkz\DEAccounting\Http\Livewire\PaymentComponent::class);
        Livewire::component('de-accounting::new-income-revenue', \Hilinkz\DEAccounting\Http\Livewire\IncomeRevenueComponent::class);
        Livewire::component('de-accounting::new-loan-investment', \Hilinkz\DEAccounting\Http\Livewire\LoanInvestmentComponent::class);
        Livewire::component('de-accounting::new-loan-invreturn', \Hilinkz\DEAccounting\Http\Livewire\LoanInvReturnComponent::class);
        Livewire::component('de-accounting::new-security-deposit', \Hilinkz\DEAccounting\Http\Livewire\SecurityDepositComponent::class);
        Livewire::component('de-accounting::new-expense', \Hilinkz\DEAccounting\Http\Livewire\ExpenseComponent::class);
        Livewire::component('de-accounting::journal-search-component', \Hilinkz\DEAccounting\Http\Livewire\JournalSearchComponent::class);
        Livewire::component('de-accounting::de-journal-search', \Hilinkz\DEAccounting\Http\Livewire\DeJournalSearchComponent::class);
        Livewire::component('de-accounting::de-ledger-search', \Hilinkz\DEAccounting\Http\Livewire\DeLedgerSearchComponent::class);
        Livewire::component('de-accounting::account-search-component', \Hilinkz\DEAccounting\Http\Livewire\AccountSearchComponent::class);

        // Load Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'de-accounting');
    }
}
