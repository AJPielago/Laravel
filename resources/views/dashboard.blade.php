@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\DB;

    $totalOrders = \App\Models\Order::count();
    $completedOrders = \App\Models\Order::where('status', 'completed')->count();
    $pendingOrders = \App\Models\Order::where('status', 'pending')->count();
    $recentOrders = \App\Models\Order::with('user')
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
    
    $totalProducts = \App\Models\Product::count();
    $activeProducts = \App\Models\Product::where('is_deleted', false)->count();
    $totalUsers = \App\Models\User::count();
    $activeUsers = \App\Models\User::where('is_active', true)->count();

    // Default date range: last 30 days
    $startDate = request('start_date', now()->subDays(30)->format('Y-m-d'));
    $endDate = request('end_date', now()->format('Y-m-d'));

    // Sales by Date Range
    $salesByDate = \App\Models\Order::select(
        DB::raw('DATE(created_at) as sale_date'),
        DB::raw('SUM(total) as total_sales')
    )
    ->where('status', 'delivered')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->groupBy('sale_date')
    ->orderBy('sale_date')
    ->get();

    // Product Sales Percentages
    $productSales = \App\Models\Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
        ->join('products', 'order_items.product_id', '=', 'products.id')
        ->select(
            'products.name', 
            DB::raw('SUM(order_items.quantity * order_items.price) as total_product_sales')
        )
        ->where('orders.status', 'delivered')
        ->whereBetween('orders.created_at', [$startDate, $endDate])
        ->groupBy('products.name')
        ->orderByDesc('total_product_sales')
        ->get();

    $totalSales = $productSales->sum('total_product_sales');
    $productSalesPercentages = $productSales->map(function($product) use ($totalSales) {
        return [
            'name' => $product->name,
            'percentage' => round(($product->total_product_sales / $totalSales) * 100, 2)
        ];
    });

    $productSalesLabels = $productSalesPercentages->pluck('name')->toArray();
    $productSalesData = $productSalesPercentages->pluck('percentage')->toArray();

    // Prepare sales by date for chart
    $salesDates = $salesByDate->pluck('sale_date')->toArray();
    $salesAmounts = $salesByDate->pluck('total_sales')->toArray();

    // Monthly Sales
    $monthlySales = \App\Models\Order::select(
        DB::raw('MONTH(created_at) as month'),
        DB::raw('SUM(total) as total_sales')
    )
    ->where('status', 'delivered')
    ->whereYear('created_at', now()->year)
    ->groupBy('month')
    ->orderBy('month')
    ->get();

    $monthlySalesLabels = [];
    $monthlySalesData = [];
    for ($i = 1; $i <= 12; $i++) {
        $monthName = Carbon::create()->month($i)->format('M');
        $monthlySalesLabels[] = $monthName;
        
        $monthSales = $monthlySales->firstWhere('month', $i);
        $monthlySalesData[] = $monthSales ? round($monthSales->total_sales, 2) : 0;
    }

    // Yearly Sales (Last 5 Years)
    $yearlySales = \App\Models\Order::select(
        DB::raw('YEAR(created_at) as year'),
        DB::raw('SUM(total) as total_sales')
    )
    ->where('status', 'delivered')
    ->whereBetween('created_at', [now()->subYears(4), now()])
    ->groupBy('year')
    ->orderBy('year')
    ->get();

    $yearlySalesLabels = [];
    $yearlySalesData = [];
    for ($i = 4; $i >= 0; $i--) {
        $year = now()->subYears($i)->year;
        $yearlySalesLabels[] = (string)$year;
        
        $yearSales = $yearlySales->firstWhere('year', $year);
        $yearlySalesData[] = $yearSales ? round($yearSales->total_sales, 2) : 0;
    }
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lara Shop - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body class="bg-gray-50 text-gray-800">
@include('layouts.header')
    <main class="min-h-[calc(100vh-8rem)] bg-gradient-to-r from-indigo-500 to-purple-500 py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg p-8">
                <h1 class="text-3xl font-bold text-indigo-600 mb-6">Dashboard</h1>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-lg shadow-lg">
                        <h2 class="text-lg font-semibold">Total Orders</h2>
                        <p class="text-3xl font-bold mt-2">{{ $totalOrders }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-lg shadow-lg">
                        <h2 class="text-lg font-semibold">Completed Orders</h2>
                        <p class="text-3xl font-bold mt-2">{{ $completedOrders }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-red-500 to-red-600 text-white p-6 rounded-lg shadow-lg">
                        <h2 class="text-lg font-semibold">Pending Orders</h2>
                        <p class="text-3xl font-bold mt-2">{{ $pendingOrders }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-lg shadow-lg">
                        <h2 class="text-lg font-semibold">Active Users</h2>
                        <p class="text-3xl font-bold mt-2">{{ $activeUsers }}</p>
                    </div>
                </div>

                <!-- Recent Orders Table -->
                <div class="mt-10">
                    <h2 class="text-2xl font-semibold text-indigo-600 mb-4">Recent Orders</h2>
                    <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b">
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Order ID</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Customer</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($recentOrders as $order)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 text-sm text-gray-700">#{{ $order->id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $order->user->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ ucfirst($order->status) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">${{ number_format($order->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Sales Bar Chart -->
                    <div class="bg-white shadow-lg rounded-lg p-6 flex flex-col">
                        <h2 class="text-xl font-semibold text-indigo-600 mb-4">Sales Over Time</h2>
                        
                        <!-- Date Range Picker -->
                        <form method="get" class="mb-4 flex space-x-4 items-center">
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700">From:</label>
                                <input 
                                    type="text" 
                                    name="start_date" 
                                    id="start_date" 
                                    value="{{ $startDate }}"
                                    class="form-input rounded-md shadow-sm mt-1 block w-36"
                                    placeholder="Start Date"
                                >
                            </div>
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700">To:</label>
                                <input 
                                    type="text" 
                                    name="end_date" 
                                    id="end_date" 
                                    value="{{ $endDate }}"
                                    class="form-input rounded-md shadow-sm mt-1 block w-36"
                                    placeholder="End Date"
                                >
                            </div>
                            <button 
                                type="submit" 
                                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition"
                            >
                                Apply
                            </button>
                        </form>
                        
                        <div class="flex-grow">
                            <canvas id="salesBarChart" class="w-full h-64"></canvas>
                        </div>
                    </div>

                    <!-- Product Sales Pie Chart -->
                    <div class="bg-white shadow-lg rounded-lg p-6 flex flex-col">
                        <h2 class="text-xl font-semibold text-indigo-600 mb-4">Product Sales Percentage</h2>
                        <div class="flex-grow">
                            <canvas id="productSalesPieChart" class="w-full h-64"></canvas>
                        </div>
                    </div>

                    <!-- Monthly Sales Chart -->
                    <div class="bg-white shadow-lg rounded-lg p-6 flex flex-col">
                        <h2 class="text-xl font-semibold text-indigo-600 mb-4">Monthly Sales ({{ now()->year }})</h2>
                        <div class="flex-grow">
                            <canvas id="monthlySalesChart" class="w-full h-64"></canvas>
                        </div>
                    </div>

                    <!-- Yearly Sales Chart -->
                    <div class="bg-white shadow-lg rounded-lg p-6 flex flex-col">
                        <h2 class="text-xl font-semibold text-indigo-600 mb-4">Yearly Sales (Last 5 Years)</h2>
                        <div class="flex-grow">
                            <canvas id="yearlySalesChart" class="w-full h-64"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('layouts.footer')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Date Range Picker
            flatpickr("#start_date", {
                dateFormat: "Y-m-d",
                maxDate: "today"
            });
            flatpickr("#end_date", {
                dateFormat: "Y-m-d",
                maxDate: "today"
            });

            // Sales Bar Chart
            new Chart(document.getElementById('salesBarChart'), {
                type: 'bar',
                data: {
                    labels: @json($salesDates),
                    datasets: [{
                        label: 'Daily Sales ($)',
                        data: @json($salesAmounts),
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toFixed(2);
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '$' + context.parsed.y.toFixed(2);
                                }
                            }
                        }
                    }
                }
            });

            // Product Sales Pie Chart
            new Chart(document.getElementById('productSalesPieChart'), {
                type: 'pie',
                data: {
                    labels: @json($productSalesLabels),
                    datasets: [{
                        data: @json($productSalesData),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed + '%';
                                }
                            }
                        }
                    }
                }
            });

            // Monthly Sales Chart
            new Chart(document.getElementById('monthlySalesChart'), {
                type: 'line',
                data: {
                    labels: @json($monthlySalesLabels),
                    datasets: [{
                        label: 'Monthly Sales ($)',
                        data: @json($monthlySalesData),
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.3,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Yearly Sales Chart
            new Chart(document.getElementById('yearlySalesChart'), {
                type: 'line',
                data: {
                    labels: @json($yearlySalesLabels),
                    datasets: [{
                        label: 'Yearly Sales ($)',
                        data: @json($yearlySalesData),
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.3,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
