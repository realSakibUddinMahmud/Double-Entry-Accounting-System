@extends('layouts.app-admin')
@section('title', 'Dashboard')

@section('content')

    {{-- Sales, Purchase, Received, Due Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <div>
                    <h4 class="fw-bold mb-0">Welcome, {{ Auth::user()->name ?? 'User' }}</h4>
                </div>
                <div class="btn-group" role="group" aria-label="Data Filter">
                    <button type="button" class="btn btn-outline-primary filter-btn" data-range="today" id="btn-today">Today</button>
                    <button type="button" class="btn btn-outline-primary filter-btn" data-range="week">Week</button>
                    <button type="button" class="btn btn-outline-primary filter-btn" data-range="month">Month</button>
                    <button type="button" class="btn btn-outline-primary filter-btn" data-range="year">Year</button>
                    <button type="button" class="btn btn-outline-primary filter-btn active" data-range="lifetime" id="btn-lifetime">Lifetime</button>
                </div>
            </div>
            <div class="row position-relative" id="summary-cards">
                <!-- Loader -->
                <div id="summary-loader" style="display:none; position:absolute; left:0; top:0; width:100%; height:100%; background:rgba(255,255,255,0.7); z-index:10; align-items:center; justify-content:center;">
                    <div class="spinner-border text-primary" role="status" style="width:3rem; height:3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card dash-widget w-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="dash-widgetimg">
                                <span><img src="assets/img/icons/dash2.svg" alt="img"></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5 class="mb-1">
                                    <span id="totalSaleAmount">{{ number_format($totalSaleAmount, 2) }}</span>
                                </h5>
                                <p class="mb-0">Total Sales</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card dash-widget dash1 w-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="dash-widgetimg">
                                <span><img src="assets/img/icons/dash4.svg" alt="img"></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5 class="mb-1">
                                    <span id="totalPurchaseAmount">{{ number_format($totalPurchaseAmount, 2) }}</span>
                                </h5>
                                <p class="mb-0">Total Purchase</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card dash-widget dash2 w-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="dash-widgetimg">
                                <span><img src="assets/img/icons/dash3.svg" alt="img"></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5 class="mb-1">
                                    <span id="totalReceivedAmount">{{ number_format($totalReceivedAmount, 2) }}</span>
                                </h5>
                                <p class="mb-0">Total Received</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card dash-widget dash3 w-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="dash-widgetimg">
                                <span><img src="assets/img/icons/dash1.svg" alt="img"></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5 class="mb-1">
                                    <span id="totalDueAmount">{{ number_format($totalDueAmount, 2) }}</span>
                                </h5>
                                <p class="mb-0">Total Due</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- The rest of your dashboard remains unchanged --}}
    <div class="row">
        <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="dash-count bg-primary w-100">
                <div class="dash-counts">
                    <h4 class="mb-1">{{ $totalCustomerCount }}</h4>
                    <p class="text-white mb-0">Customers</p>
                </div>
                <div class="dash-imgs">
                    <i data-feather="user"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="dash-count das1 bg-cyan-900 w-100">
                <div class="dash-counts">
                    <h4 class="mb-1">{{ $totalSupplierCount }}</h4>
                    <p class="text-white mb-0">Suppliers</p>
                </div>
                <div class="dash-imgs">
                    <i data-feather="user-check"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="dash-count das2 bg-dark w-100">
                <div class="dash-counts">
                    <h4 class="mb-1">{{ $totalPurchaseCount }}</h4>
                    <p class="text-white mb-0">Purchase Invoice</p>
                </div>
                <div class="dash-imgs">
                    <img src="assets/img/icons/file-text-icon-01.svg" class="img-fluid" alt="icon">
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="dash-count das3 bg-success w-100">
                <div class="dash-counts">
                    <h4 class="mb-1">{{ $totalSalesCount }}</h4>
                    <p class="text-white mb-0">Sales Invoice</p>
                </div>
                <div class="dash-imgs">
                    <i data-feather="file"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-sm-12 col-12 d-flex">
            <div class="card flex-fill w-100">
                <div class="card-body pb-0">
                    <h5 class="card-title mb-0">Sales & Purchase</h5>
                    <div id="sales_vs_purchase_chart" style="height: 450px;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Counter animation function
        function animateCounter(element, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                element.innerText = (start + (end - start) * progress).toLocaleString(undefined, {minimumFractionDigits: 2});
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                } else {
                    element.innerText = end.toLocaleString(undefined, {minimumFractionDigits: 2});
                }
            };
            window.requestAnimationFrame(step);
        }

        // Only define the variable once globally
        window.salesVsPurchaseChart = window.salesVsPurchaseChart || null;

        function renderSalesVsPurchaseChart() {
            if (window.salesVsPurchaseChart) {
                window.salesVsPurchaseChart.destroy();
            }
            var options = {
                chart: {
                    type: 'bar',
                    height: 450,
                    stacked: false,
                    toolbar: { show: false }
                },
                series: [
                    {
                        name: 'Sales',
                        data: @json($salesData)
                    },
                    {
                        name: 'Purchase',
                        data: @json($purchasesData)
                    }
                ],
                xaxis: {
                    categories: @json($months)
                },
                yaxis: {
                    title: { text: 'Amount' }
                },
                colors: ['#3EB780', '#FC7240'],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '40%',
                        endingShape: 'rounded'
                    }
                },
                dataLabels: { enabled: false },
                legend: { position: 'top' }
            };

            window.salesVsPurchaseChart = new ApexCharts(document.querySelector("#sales_vs_purchase_chart"), options);
            window.salesVsPurchaseChart.render();
        }

        // Only run once per page load
        if (!window.salesVsPurchaseChartInitialized) {
            document.addEventListener('DOMContentLoaded', renderSalesVsPurchaseChart);
            window.salesVsPurchaseChartInitialized = true;
        }

        // Data filter button logic (AJAX example)
        // Prevent duplicate requests
        let ongoingRequest = null;
        let lastRequestRange = null;
        let eventListenersAttached = false;

        // Only attach event listeners once
        if (!eventListenersAttached) {
            document.querySelectorAll('.filter-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    let range = this.getAttribute('data-range');
                    
                    // Prevent duplicate requests for the same range
                    if (lastRequestRange === range && ongoingRequest) {
                        console.log(`Preventing duplicate request for range: ${range}`);
                        return;
                    }
                    
                    // Remove active class from all buttons
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show loader only on filter change
                    document.getElementById('summary-loader').style.display = 'flex';
                    
                    // Cancel ongoing request if exists
                    if (ongoingRequest) {
                        console.log(`Waiting for ongoing request to complete before making new request for range: ${range}`);
                        // If there's an ongoing request, wait for it to complete
                        ongoingRequest.then(() => {
                            makeRequest(range);
                        });
                        return;
                    }
                    
                    makeRequest(range);
                });
            });
            eventListenersAttached = true;
            console.log('Event listeners attached successfully');
        }

        function makeRequest(range) {
            // Store the current range
            lastRequestRange = range;
            
            console.log(`Making API request for range: ${range}`);
            
            // Create the request promise
            ongoingRequest = fetch(`{{ url('/dashboard/summary-data') }}?range=${range}`)
                .then(response => response.json())
                .then(data => {
                    console.log(`Received data for range: ${range}`, data);
                    
                    // Animate counters
                    animateCounter(document.getElementById('totalSaleAmount'), parseFloat(document.getElementById('totalSaleAmount').innerText.replace(/,/g, '')), parseFloat(data.totalSaleAmount), 800);
                    animateCounter(document.getElementById('totalPurchaseAmount'), parseFloat(document.getElementById('totalPurchaseAmount').innerText.replace(/,/g, '')), parseFloat(data.totalPurchaseAmount), 800);
                    animateCounter(document.getElementById('totalReceivedAmount'), parseFloat(document.getElementById('totalReceivedAmount').innerText.replace(/,/g, '')), parseFloat(data.totalReceivedAmount), 800);
                    animateCounter(document.getElementById('totalDueAmount'), parseFloat(document.getElementById('totalDueAmount').innerText.replace(/,/g, '')), parseFloat(data.totalDueAmount), 800);
                    
                    // Hide loader
                    document.getElementById('summary-loader').style.display = 'none';
                })
                .catch((error) => {
                    console.error('Error fetching summary data:', error);
                    document.getElementById('summary-loader').style.display = 'none';
                })
                .finally(() => {
                    // Clear the ongoing request
                    ongoingRequest = null;
                    console.log(`Request completed for range: ${range}`);
                });
        }

        // Trigger lifetime data on initial load (no loader) - ONLY ONCE
        let initialLoadCompleted = false;
        
        // Global flag to prevent multiple DOMContentLoaded executions
        if (!window.dashboardInitialized) {
            document.addEventListener('DOMContentLoaded', function() {
                if (!initialLoadCompleted) {
                    console.log('Dashboard initialized - setting lifetime as active');
                    // Simulate click on lifetime button without making an API call
                    // since the data is already loaded from the server
                    document.getElementById('btn-lifetime').classList.add('active');
                    initialLoadCompleted = true;
                    window.dashboardInitialized = true;
                }
            });
        }
    </script>
@endsection
