<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Debt;
use Carbon\Carbon;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use Illuminate\Support\Collection;


class TransactionReportController extends Controller
{
    /**
     * Generate a summary report of all transactions
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function transactionSummary(Request $request)
    {
        // Parse date range from request or use defaults
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->subMonth();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::now();

        $endDate = $endDate->copy()->addDay();

        // Get transactions within date range
        $transactions = Transaction::with(['customer', 'items.service'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Calculate summary statistics
        $totalRevenue = $transactions->sum('total_amount');
        $totalPaid = $transactions->sum('amount_paid');
        $outstandingAmount = $totalRevenue - $totalPaid;

        $paidTransactions = $transactions->where('payment_status', 'paid')->count();
        $partialTransactions = $transactions->where('payment_status', 'partial')->count();
        $unpaidTransactions = $transactions->where('payment_status', 'unpaid')->count();

        // Group transactions by day for trend analysis
        $dailyTotals = $transactions
            ->groupBy(function ($transaction) {
                return Carbon::parse($transaction->created_at)->format('Y-m-d');
            })
            ->map(function ($dayTransactions) {
                return [
                    'count' => $dayTransactions->count(),
                    'revenue' => $dayTransactions->sum('total_amount'),
                    'paid' => $dayTransactions->sum('amount_paid')
                ];
            });

        // Get top services by revenue
        $topServices = collect();
        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                $serviceName = $item->service->name;
                $revenue = $item->price * $item->quantity;

                if ($topServices->has($serviceName)) {
                    $serviceData = $topServices->get($serviceName);
                    $serviceData['revenue'] += $revenue;
                    $serviceData['count'] += $item->quantity;
                    $topServices->put($serviceName, $serviceData);
                } else {
                    $topServices->put($serviceName, [
                        'revenue' => $revenue,
                        'count' => $item->quantity
                    ]);
                }
            }
        }
        $topServices = $topServices->sortByDesc('revenue')->take(5);

        // Format response
        $response = [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->subDay()->format('Y-m-d'),
            ],
            'summary' => [
                'total_transactions' => $transactions->count(),
                'total_revenue' => $totalRevenue,
                'total_paid' => $totalPaid,
                'outstanding_amount' => $outstandingAmount,
                'payment_status' => [
                    'paid' => $paidTransactions,
                    'partial' => $partialTransactions,
                    'unpaid' => $unpaidTransactions,
                ]
            ],
            'daily_trends' => $dailyTotals,
            'top_services' => $topServices,
        ];

        return response()->json($response);
    }
    /**
     * Generate a customer activity report
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function customerActivity(Request $request)
    {
        // Parse date range from request or use defaults
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->subMonth();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::now();

        // Store the original end date before modification
        $originalEndDate = $endDate->copy();
        $endDate = $endDate->addDay();

        // Optional customer ID filter
        $customerId = $request->input('customer_id');

        // Build query with eager loading
        $query = Transaction::with(['customer', 'items.service'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        $transactions = $query->get();

        // Group transactions by customer and process data
        $customerActivity = $transactions
            ->groupBy('customer_id')
            ->map(function ($customerTransactions) {
                // Check for customer existence
                $customer = $customerTransactions->first()->customer ?? null;
                if (!$customer) {
                    return null; // Skip if no customer is found
                }

                // Initialize services collection
                $services = collect();
                foreach ($customerTransactions as $transaction) {
                    foreach ($transaction->items as $item) {
                        // Safely handle missing service
                        $serviceName = $item->service ? $item->service->name : 'Unknown';
                        $revenue = $item->price * $item->quantity;

                        if ($services->has($serviceName)) {
                            $serviceData = $services->get($serviceName);
                            $serviceData['revenue'] += $revenue;
                            $serviceData['count'] += $item->quantity;
                            $services->put($serviceName, $serviceData);
                        } else {
                            $services->put($serviceName, [
                                'revenue' => $revenue,
                                'count' => $item->quantity
                            ]);
                        }
                    }
                }

                // Sort services by revenue in descending order
                $services = $services->sortByDesc('revenue');

                return [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                    ],
                    'transactions' => [
                        'count' => $customerTransactions->count(),
                        'total_spent' => $customerTransactions->sum('total_amount'),
                        'total_paid' => $customerTransactions->sum('amount_paid'),
                        'outstanding' => $customerTransactions->sum('total_amount') - $customerTransactions->sum('amount_paid'),
                    ],
                    'services_purchased' => $services,
                    'last_transaction' => Carbon::parse($customerTransactions->sortByDesc('created_at')->first()->created_at)->format('Y-m-d'),
                ];
            })
            ->filter() // Remove null entries
            ->sortByDesc(function ($customer) {
                return $customer['transactions']['total_spent'];
            });

        // Format the response
        $response = [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $originalEndDate->format('Y-m-d'),
            ],
            'customer_count' => $customerActivity->count(),
            'customer_activity' => $customerActivity->values(),
        ];

        return response()->json($response);
    }
    /**
     * Generate a debt report
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function debtReport(Request $request)
    {
        // Get all debts with customer and transaction information
        $debts = Debt::with(['customer', 'transaction'])
            ->where('status', 'pending')
            ->get();

        // Group debts by customer
        $customerDebts = $debts
            ->groupBy('customer_id')
            ->map(function ($customerDebts) {
                $customer = $customerDebts->first()->customer;

                // Get debts details
                $debtDetails = $customerDebts->map(function ($debt) {
                    return [
                        'transaction_id' => $debt->transaction_id,
                        'transaction_date' => Carbon::parse($debt->transaction->created_at)->format('Y-m-d'),
                        'amount' => $debt->amount,
                        'days_outstanding' => Carbon::parse($debt->transaction->created_at)->diffInDays(Carbon::now()),
                    ];
                });

                return [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                    ],
                    'total_debt' => $customerDebts->sum('amount'),
                    'debt_count' => $customerDebts->count(),
                    'average_days_outstanding' => $debtDetails->avg('days_outstanding'),
                    'debt_details' => $debtDetails,
                ];
            })
            ->sortByDesc('total_debt');

        // Format response
        $response = [
            'total_debt' => $debts->sum('amount'),
            'total_customers_with_debt' => $customerDebts->count(),
            'customer_debts' => $customerDebts->values(),
        ];

        return response()->json($response);
    }

    /**
     * Generate a service performance report
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function servicePerformance(Request $request)
    {
        // Parse date range from request or use defaults
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->subMonth();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::now();

        // Add a day to end date to include the end date in the query
        $endDate = $endDate->copy()->addDay();

        // Optional service ID filter
        $serviceId = $request->input('service_id');

        // Get all transaction items within date range
        $query = Transaction::with(['items.service'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        $transactions = $query->get();

        // Process transaction items to get service statistics
        $serviceStats = collect();

        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                if ($serviceId && $item->service_id != $serviceId) {
                    continue;
                }

                $serviceId = $item->service_id;
                $serviceName = $item->service->name;
                $revenue = $item->price * $item->quantity;
                $date = Carbon::parse($transaction->created_at)->format('Y-m-d');

                if (!$serviceStats->has($serviceId)) {
                    $serviceStats[$serviceId] = [
                        'id' => $serviceId,
                        'name' => $serviceName,
                        'total_revenue' => 0,
                        'total_quantity' => 0,
                        'transaction_count' => 0,
                        'daily_sales' => collect(),
                    ];
                }

                // Update statistics
                $serviceStats[$serviceId]['total_revenue'] += $revenue;
                $serviceStats[$serviceId]['total_quantity'] += $item->quantity;
                $serviceStats[$serviceId]['transaction_count']++;

                // Add to daily sales
                if (!$serviceStats[$serviceId]['daily_sales']->has($date)) {
                    $serviceStats[$serviceId]['daily_sales'][$date] = [
                        'date' => $date,
                        'revenue' => 0,
                        'quantity' => 0,
                    ];
                }

                $serviceStats[$serviceId]['daily_sales'][$date]['revenue'] += $revenue;
                $serviceStats[$serviceId]['daily_sales'][$date]['quantity'] += $item->quantity;
            }
        }

        // Format each service's data
        $serviceStats = $serviceStats->map(function ($service) {
            // Convert daily sales to array and sort by date
            $dailySales = $service['daily_sales']->sortKeys()->values();

            return [
                'id' => $service['id'],
                'name' => $service['name'],
                'total_revenue' => $service['total_revenue'],
                'total_quantity' => $service['total_quantity'],
                'transaction_count' => $service['transaction_count'],
                'average_price' => $service['total_revenue'] / $service['total_quantity'],
                'daily_sales' => $dailySales,
            ];
        })
        ->sortByDesc('total_revenue')
        ->values();

        // Format response
        $response = [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->subDay()->format('Y-m-d'),
            ],
            'service_count' => $serviceStats->count(),
            'total_revenue' => $serviceStats->sum('total_revenue'),
            'service_performance' => $serviceStats,
        ];

        return response()->json($response);
    }

    /**
     * Generate Excel dashboard report
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function dashboardExcel(Request $request)
    {
        try {
            // Parse date range
            $startDate = $request->input('start_date')
                ? Carbon::parse($request->input('start_date'))
                : Carbon::now()->subMonth();

            $endDate = $request->input('end_date')
                ? Carbon::parse($request->input('end_date'))
                : Carbon::now();

            // Add a day to end date to include the end date in the query
            $endDateQuery = $endDate->copy()->addDay();

            // Get transactions
            $transactions = Transaction::with(['customer', 'items.service'])
                ->whereBetween('created_at', [$startDate, $endDateQuery])
                ->get();

            // Create a new Spreadsheet
            $spreadsheet = new Spreadsheet();

            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('Your Company')
                ->setLastModifiedBy('Your Company')
                ->setTitle('Business Dashboard')
                ->setSubject('Business Performance Report')
                ->setDescription('Report generated on ' . Carbon::now()->format('Y-m-d'));

            // Create Overview sheet
            $this->createOverviewSheet($spreadsheet, $transactions, $startDate, $endDate);

            // Create Services sheet
            $this->createServicesSheet($spreadsheet, $transactions);

            // Create Customers sheet
            $this->createCustomersSheet($spreadsheet, $transactions);

            // Create Debts sheet
            $this->createDebtsSheet($spreadsheet);

            // Create Charts sheet
            $this->createChartsSheet($spreadsheet, $transactions, $startDate, $endDate);

            // Set active sheet to first
            $spreadsheet->setActiveSheetIndex(0);

            // Create Excel writer
            $writer = new Xlsx($spreadsheet);

            // Set filename
            $filename = 'dashboard_report_' . Carbon::now()->format('Y-m-d') . '.xlsx';

            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'dashboard');
            $writer->save($tempFile);

            // Return file for download
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not generate Excel report: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Generate PDF report
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function summaryPdf(Request $request)
    {
        try {
            // Parse date range
            $startDate = $request->input('start_date')
                ? Carbon::parse($request->input('start_date'))
                : Carbon::now()->subMonth();

            $endDate = $request->input('end_date')
                ? Carbon::parse($request->input('end_date'))
                : Carbon::now();

            // Add a day to end date to include the end date in the query
            $endDateQuery = $endDate->copy()->addDay();

            // Get transactions
            $transactions = Transaction::with(['customer', 'items.service'])
                ->whereBetween('created_at', [$startDate, $endDateQuery])
                ->get();

            // Calculate summary statistics
            $totalRevenue = $transactions->sum('total_amount');
            $totalPaid = $transactions->sum('amount_paid');
            $outstandingAmount = $totalRevenue - $totalPaid;

            $paidTransactions = $transactions->where('payment_status', 'paid')->count();
            $partialTransactions = $transactions->where('payment_status', 'partial')->count();
            $unpaidTransactions = $transactions->where('payment_status', 'unpaid')->count();

            // Get top services
            $topServices = $transactions->flatMap(function ($transaction) {
                return $transaction->items->map(function ($item) use ($transaction) {
                    return [
                        'service_name' => $item->service->name,
                        'revenue' => $item->price * $item->quantity,
                        'count' => $item->quantity,
                    ];
                });
            })->groupBy('service_name')
              ->map(function ($group) {
                  return [
                      'revenue' => $group->sum('revenue'),
                      'count' => $group->sum('count'),
                  ];
              })
              ->sortByDesc('revenue')
              ->take(5);

            // Get top customers
            $topCustomers = $transactions->groupBy('customer_id')
                ->map(function ($customerTransactions) {
                    $customer = $customerTransactions->first()->customer;

                    return [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'total_spent' => $customerTransactions->sum('total_amount'),
                        'transaction_count' => $customerTransactions->count(),
                    ];
                })
                ->sortByDesc('total_spent')
                ->take(5);

            // Generate HTML content
            $html = $this->generateSummaryPdfHtml(
                $startDate,
                $endDate,
                $transactions,
                $totalRevenue,
                $totalPaid,
                $outstandingAmount,
                $paidTransactions,
                $partialTransactions,
                $unpaidTransactions,
                $topServices,
                $topCustomers
            );

            // Generate PDF
            $pdf = PDF::loadHTML($html);

            // Set filename
            $filename = 'business_summary_' . Carbon::now()->format('Y-m-d') . '.pdf';

            // Return PDF for download
            return $pdf->download($filename);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not generate PDF report: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Create Overview sheet for Excel report
     */
    private function createOverviewSheet($spreadsheet, $transactions, $startDate, $endDate)
    {
        // Get the active sheet (Overview)
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Overview');

        // Set header
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'BUSINESS PERFORMANCE DASHBOARD');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set date range
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Period: ' . $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Summary metrics
        $sheet->setCellValue('A4', 'SUMMARY METRICS');
        $sheet->getStyle('A4')->getFont()->setBold(true);

        // Calculate summary statistics
        $totalRevenue = $transactions->sum('total_amount');
        $totalPaid = $transactions->sum('amount_paid');
        $outstandingAmount = $totalRevenue - $totalPaid;

        $paidTransactions = $transactions->where('payment_status', 'paid')->count();
        $partialTransactions = $transactions->where('payment_status', 'partial')->count();
        $unpaidTransactions = $transactions->where('payment_status', 'unpaid')->count();

        // Key metrics - first column
        $sheet->setCellValue('A6', 'Total Transactions:');
        $sheet->setCellValue('B6', $transactions->count());

        $sheet->setCellValue('A7', 'Total Revenue:');
        $sheet->setCellValue('B7', number_format($totalRevenue, 2) . ' Fbu');

        $sheet->setCellValue('A8', 'Total Paid:');
        $sheet->setCellValue('B8', number_format($totalPaid, 2) . ' Fbu');

        $sheet->setCellValue('A9', 'Outstanding Amount:');
        $sheet->setCellValue('B9', number_format($outstandingAmount, 2) . ' Fbu');

        // Key metrics - second column
        $sheet->setCellValue('D6', 'Paid Transactions:');
        $sheet->setCellValue('E6', $paidTransactions);

        $sheet->setCellValue('D7', 'Partial Transactions:');
        $sheet->setCellValue('E7', $partialTransactions);

        $sheet->setCellValue('D8', 'Unpaid Transactions:');
        $sheet->setCellValue('E8', $unpaidTransactions);

        // Style metrics
        $sheet->getStyle('A6:A9')->getFont()->setBold(true);
        $sheet->getStyle('D6:D8')->getFont()->setBold(true);

        // Daily transactions
        $sheet->setCellValue('A11', 'DAILY TRANSACTION SUMMARY');
        $sheet->getStyle('A11')->getFont()->setBold(true);

        $sheet->setCellValue('A12', 'Date');
        $sheet->setCellValue('B12', 'Transaction Count');
        $sheet->setCellValue('C12', 'Revenue');
        $sheet->setCellValue('D12', 'Amount Paid');

        // Style header row
        $sheet->getStyle('A12:D12')->getFont()->setBold(true);
        $sheet->getStyle('A12:D12')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9D9D9');

        // Group transactions by day
        $dailyTotals = $transactions
            ->groupBy(function ($transaction) {
                return Carbon::parse($transaction->created_at)->format('Y-m-d');
            })
            ->sortKeys();

        // Fill daily data
        $row = 13;
        foreach ($dailyTotals as $date => $dayTransactions) {
            $sheet->setCellValue('A' . $row, $date);
            $sheet->setCellValue('B' . $row, $dayTransactions->count());
            $sheet->setCellValue('C' . $row, number_format($dayTransactions->sum('total_amount'), 2) . ' Fbu');
            $sheet->setCellValue('D' . $row, number_format($dayTransactions->sum('amount_paid'), 2) . ' Fbu');

            // Alternate row styling
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F5F5F5');
            }

            $row++;
        }

        // Add border to table
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'AAAAAA'],
                ],
            ],
        ];
        $sheet->getStyle('A12:D' . ($row - 1))->applyFromArray($borderStyle);

        // Auto-size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Create Services sheet for Excel report
     */
    private function createServicesSheet($spreadsheet, $transactions)
    {
        // Add a new sheet
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Services');

        // Set header
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'SERVICE PERFORMANCE');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table headers
        $sheet->setCellValue('A3', 'Service');
        $sheet->setCellValue('B3', 'Total Quantity');
        $sheet->setCellValue('C3', 'Total Revenue');
        $sheet->setCellValue('D3', 'Average Price');
        $sheet->setCellValue('E3', 'Transaction Count');

        // Style header row
        $sheet->getStyle('A3:E3')->getFont()->setBold(true);
        $sheet->getStyle('A3:E3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9D9D9');

        // Process service data
        $serviceStats = collect();

        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                $serviceId = $item->service_id;
                $serviceName = $item->service->name;
                $revenue = $item->price * $item->quantity;

                if (!$serviceStats->has($serviceId)) {
                    $serviceStats[$serviceId] = [
                        'name' => $serviceName,
                        'total_revenue' => 0,
                        'total_quantity' => 0,
                        'transaction_count' => 0,
                    ];
                }

                // Update statistics
                $serviceStats[$serviceId]['total_revenue'] += $revenue;
                $serviceStats[$serviceId]['total_quantity'] += $item->quantity;
                $serviceStats[$serviceId]['transaction_count']++;
            }
        }

        // Sort services by revenue
        $serviceStats = $serviceStats->sortByDesc('total_revenue');

        // Fill service data
        $row = 4;
        foreach ($serviceStats as $service) {
            $sheet->setCellValue('A' . $row, $service['name']);
            $sheet->setCellValue('B' . $row, $service['total_quantity']);
            $sheet->setCellValue('C' . $row, number_format($service['total_revenue'], 2) . ' Fbu');
            $sheet->setCellValue('D' . $row, number_format($service['total_revenue'] / $service['total_quantity'], 2) . ' Fbu');
            $sheet->setCellValue('E' . $row, $service['transaction_count']);

            // Alternate row styling
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F5F5F5');
            }

            $row++;
        }

        // Add border to table
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'AAAAAA'],
                ],
            ],
        ];
        $sheet->getStyle('A3:E' . ($row - 1))->applyFromArray($borderStyle);

        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    private function generateSummaryPdfHtml(
        $startDate,
        $endDate,
        $transactions,
        $totalRevenue,
        $totalPaid,
        $outstandingAmount,
        $paidTransactions,
        $partialTransactions,
        $unpaidTransactions,
        $topServices,
        $topCustomers
    ) {
        // Load a view or build HTML content as a string
        return view('pdf.summary', compact(
            'startDate',
            'endDate',
            'transactions',
            'totalRevenue',
            'totalPaid',
            'outstandingAmount',
            'paidTransactions',
            'partialTransactions',
            'unpaidTransactions',
            'topServices',
            'topCustomers'
        ))->render();
    }


}
