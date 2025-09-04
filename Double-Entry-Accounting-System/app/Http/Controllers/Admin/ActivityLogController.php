<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Models\Audit;
use Spatie\Multitenancy\Models\Tenant;
use Carbon\Carbon;
use App\Models\Sale;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the activity log index page.
     */
    public function index(Request $request)
    {
        // Get current tenant
        $tenant = Tenant::current();
        
        // Get filters from request
        $event = $request->get('event');
        $model = $request->get('model');
        $user = $request->get('user');
        $dateRange = $request->get('date_range', 'month'); // Default to last 30 days
        
        // Build query for audits using tenant connection
        $query = DB::connection('tenant')->table('audits')
            ->leftJoin('users', 'audits.user_id', '=', 'users.id')
            ->select([
                'audits.*',
                'users.name as user_name',
                'users.email as user_email'
            ])
            ->whereNotIn('audits.auditable_type', [
                'Hilinkz\\DEAccounting\\Models\\DeTask',
                'Hilinkz\\DEAccounting\\Models\\DeAccountTransaction'
            ])
            ->orderBy('audits.created_at', 'desc');
        
        // Apply filters
        if ($event) {
            $query->where('audits.event', $event);
        }
        
        if ($model) {
            $query->where('audits.auditable_type', $model);
        }
        
        if ($user) {
            $query->where('audits.user_id', $user);
        }
        
        // Apply date filter - default to last 30 days
        if ($dateRange !== 'all') {
            $days = match($dateRange) {
                'today' => 1,
                'week' => 7,
                'month' => 30,
                'quarter' => 90,
                default => 30 // Default to 30 days
            };
            $query->where('audits.created_at', '>=', now()->subDays($days));
        }
        
        // Get paginated results
        $audits = $query->paginate(50);
        
        // Convert date strings to Carbon objects for proper formatting
        $audits->getCollection()->transform(function ($audit) {
            $audit->created_at = Carbon::parse($audit->created_at);
            $audit->updated_at = Carbon::parse($audit->created_at);
            
            // Add journal relationship data for DeJournal audits
            if ($audit->auditable_type === 'Hilinkz\\DEAccounting\\Models\\DeJournal') {
                $audit->journal_connection = $this->getJournalConnection($audit->auditable_id);
            }
            
            return $audit;
        });
        
        // Get filter options for dropdowns using tenant connection
        $events = DB::connection('tenant')->table('audits')
            ->whereNotIn('auditable_type', [
                'Hilinkz\\DEAccounting\\Models\\DeTask',
                'Hilinkz\\DEAccounting\\Models\\DeAccountTransaction'
            ])
            ->distinct()
            ->pluck('event');
            
        $models = DB::connection('tenant')->table('audits')
            ->whereNotIn('auditable_type', [
                'Hilinkz\\DEAccounting\\Models\\DeTask',
                'Hilinkz\\DEAccounting\\Models\\DeAccountTransaction'
            ])
            ->distinct()
            ->pluck('auditable_type');
            
        $users = DB::connection('tenant')->table('audits')
            ->whereNotIn('auditable_type', [
                'Hilinkz\\DEAccounting\\Models\\DeTask',
                'Hilinkz\\DEAccounting\\Models\\DeAccountTransaction'
            ])
            ->leftJoin('users', 'audits.user_id', '=', 'users.id')
            ->whereNotNull('users.name')
            ->distinct()
            ->pluck('users.name', 'users.id');
        
        // Get summary statistics for last 30 days only
        $stats = [
            'today' => DB::connection('tenant')->table('audits')
                ->whereNotIn('auditable_type', [
                    'Hilinkz\\DEAccounting\\Models\\DeTask',
                    'Hilinkz\\DEAccounting\\Models\\DeAccountTransaction'
                ])
                ->whereDate('created_at', today())->count(),
            'week' => DB::connection('tenant')->table('audits')
                ->whereNotIn('auditable_type', [
                    'Hilinkz\\DEAccounting\\Models\\DeTask',
                    'Hilinkz\\DEAccounting\\Models\\DeAccountTransaction'
                ])
                ->where('created_at', '>=', now()->subWeek())->count(),
            'month' => DB::connection('tenant')->table('audits')
                ->whereNotIn('auditable_type', [
                    'Hilinkz\\DEAccounting\\Models\\DeTask',
                    'Hilinkz\\DEAccounting\\Models\\DeAccountTransaction'
                ])
                ->where('created_at', '>=', now()->subMonth())->count(),
        ];
        
        return view('admin.activity-log.index', compact(
            'audits', 
            'events', 
            'models', 
            'users', 
            'stats',
            'event',
            'model',
            'user',
            'dateRange'
        ));
    }
    
    /**
     * Get journal connection information for DeJournal audits
     */
    private function getJournalConnection($journalId)
    {
        try {
            $journal = DB::connection('tenant')->table('de_journals')
                ->where('id', $journalId)
                ->first();
                
            if (!$journal) {
                return null;
            }
            
            $connection = [
                'journalable_type' => $journal->journalable_type,
                'journalable_id' => $journal->journalable_id,
                'transaction_type' => $journal->transaction_type,
                'amount' => $journal->amount,
                'date' => $journal->date,
                'note' => $journal->note
            ];
            
            // Get the related model information
            if ($journal->journalable_type && $journal->journalable_id) {
                $modelClass = $journal->journalable_type;
                
                if (class_exists($modelClass)) {
                    $relatedModel = DB::connection('tenant')->table((new $modelClass)->getTable())
                        ->where('id', $journal->journalable_id)
                        ->first();
                        
                    if ($relatedModel) {
                        $connection['related_model'] = $relatedModel;
                        
                        // Get display name based on model type
                        if (isset($relatedModel->u_id)) {
                            $connection['display_name'] = 'Invoice/Bill #' . $relatedModel->u_id;
                        } elseif (isset($relatedModel->name)) {
                            $connection['display_name'] = $relatedModel->name;
                        } elseif (isset($relatedModel->title)) {
                            $connection['display_name'] = $relatedModel->title;
                        } else {
                            $connection['display_name'] = 'ID: ' . $relatedModel->id;
                        }
                    }
                }
            }
            
            return $connection;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get activity log for a specific sale
     */
    public function getSaleActivities($saleId)
    {
        try {
            // Get current tenant
            $tenant = Tenant::current();
            
            // Debug: Check if we're getting the sale ID
            \Log::info('Getting sale activities', ['sale_id' => $saleId, 'tenant' => $tenant?->id]);
            
            // Get sale audits
            $saleAudits = DB::connection('tenant')->table('audits')
                ->leftJoin('users', 'audits.user_id', '=', 'users.id')
                ->select([
                    'audits.*',
                    'users.name as user_name',
                    'users.email as user_email'
                ])
                ->where('audits.auditable_type', 'App\\Models\\Sale')
                ->where('audits.auditable_id', $saleId)
                ->orderBy('audits.created_at', 'asc') // Oldest first
                ->get();

            // Get journal audits related to this sale
            $journalAudits = DB::connection('tenant')->table('audits')
                ->leftJoin('users', 'audits.user_id', '=', 'users.id')
                ->leftJoin('de_journals', 'audits.auditable_id', '=', 'de_journals.id')
                ->select([
                    'audits.*',
                    'users.name as user_name',
                    'users.email as user_email'
                ])
                ->where('audits.auditable_type', 'Hilinkz\\DEAccounting\\Models\\DeJournal')
                ->where('de_journals.journalable_type', 'App\\Models\\Sale')
                ->where('de_journals.journalable_id', $saleId)
                ->orderBy('audits.created_at', 'asc') // Oldest first
                ->get();

            // Combine and sort all audits
            $allAudits = $saleAudits->concat($journalAudits)
                ->sortByDesc('created_at') // Latest first
                ->values();

            // Transform audits to include human-readable descriptions
            $activities = $allAudits->map(function ($audit) use ($saleId) {
                $audit->created_at = Carbon::parse($audit->created_at);
                
                // Decode JSON values
                $audit->old_values = json_decode($audit->old_values, true) ?? [];
                $audit->new_values = json_decode($audit->new_values, true) ?? [];
                
                // Generate human-readable description
                $description = $this->generateActivityDescription($audit, $saleId);
                
                return [
                    'id' => $audit->id,
                    'event' => $audit->event,
                    'event_type' => $audit->auditable_type,
                    'user_name' => $audit->user_name ?? 'System',
                    'user_email' => $audit->user_email,
                    'created_at' => $audit->created_at,
                    'time_ago' => $audit->created_at->diffForHumans(),
                    'description' => $description,
                    'old_values' => $audit->old_values,
                    'new_values' => $audit->new_values
                ];
            });

            return response()->json([
                'success' => true,
                'activities' => $activities,
                'total' => $activities->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to load sale activities', [
                'sale_id' => $saleId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load activity history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activity log for a specific purchase
     */
    public function getPurchaseActivities($purchaseId)
    {
        try {
            // Get current tenant
            $tenant = Tenant::current();
            
            // Debug: Check if we're getting the purchase ID
            \Log::info('Getting purchase activities', ['purchase_id' => $purchaseId, 'tenant' => $tenant?->id]);
            
            // Get purchase audits
            $purchaseAudits = DB::connection('tenant')->table('audits')
                ->leftJoin('users', 'audits.user_id', '=', 'users.id')
                ->select([
                    'audits.*',
                    'users.name as user_name',
                    'users.email as user_email'
                ])
                ->where('audits.auditable_type', 'App\\Models\\Purchase')
                ->where('audits.auditable_id', $purchaseId)
                ->orderBy('audits.created_at', 'desc') // Latest first
                ->get();

            // Get journal audits related to this purchase
            $journalAudits = DB::connection('tenant')->table('audits')
                ->leftJoin('users', 'audits.user_id', '=', 'users.id')
                ->leftJoin('de_journals', 'audits.auditable_id', '=', 'de_journals.id')
                ->select([
                    'audits.*',
                    'users.name as user_name',
                    'users.email as user_email'
                ])
                ->where('audits.auditable_type', 'Hilinkz\\DEAccounting\\Models\\DeJournal')
                ->where('de_journals.journalable_type', 'App\\Models\\Purchase')
                ->where('de_journals.journalable_id', $purchaseId)
                ->orderBy('audits.created_at', 'desc') // Latest first
                ->get();

            // Combine and sort all audits
            $allAudits = $purchaseAudits->concat($journalAudits)
                ->sortByDesc('created_at')
                ->values();

            // Transform audits to include human-readable descriptions
            $activities = $allAudits->map(function ($audit) use ($purchaseId) {
                $audit->created_at = Carbon::parse($audit->created_at);
                
                // Decode JSON values
                $audit->old_values = json_decode($audit->old_values, true) ?? [];
                $audit->new_values = json_decode($audit->new_values, true) ?? [];
                
                // Generate human-readable description
                $description = $this->generateActivityDescription($audit, $purchaseId);
                
                return [
                    'id' => $audit->id,
                    'event' => $audit->event,
                    'event_type' => $audit->auditable_type,
                    'user_name' => $audit->user_name ?? 'System',
                    'user_email' => $audit->user_email,
                    'created_at' => $audit->created_at,
                    'time_ago' => $audit->created_at->diffForHumans(),
                    'description' => $description,
                    'old_values' => $audit->old_values,
                    'new_values' => $audit->new_values
                ];
            });

            return response()->json([
                'success' => true,
                'activities' => $activities,
                'total' => $activities->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to load purchase activities', [
                'purchase_id' => $purchaseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load activity history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate human-readable description for an audit
     */
    private function generateActivityDescription($audit, $modelId = null)
    {
        $modelName = class_basename($audit->auditable_type);
        $event = $audit->event;
        
        if ($event === 'created') {
            if ($modelName === 'Sale') {
                $uId = is_array($audit->new_values) && isset($audit->new_values['u_id']) ? $audit->new_values['u_id'] : 'N/A';
                return "Sale invoice #{$uId} was created";
            } elseif ($modelName === 'Purchase') {
                $uId = is_array($audit->new_values) && isset($audit->new_values['u_id']) ? $audit->new_values['u_id'] : 'N/A';
                return "Purchase invoice #{$uId} was created";
            } elseif ($modelName === 'DeJournal') {
                $journalData = $this->getJournalConnection($audit->auditable_id);
                if ($journalData) {
                    $amount = number_format($journalData['amount'], 2);
                    $type = $journalData['transaction_type'];
                    return "Journal entry for {$amount} ({$type}) was created";
                }
                return "Journal entry was created";
            }
            return ucfirst($modelName) . " was created";
        }
        
        if ($event === 'updated') {
            if ($modelName === 'Sale') {
                // For updates, try to get u_id from new_values first, then old_values, then use saleId as fallback
                $uId = 'N/A';
                if (is_array($audit->new_values) && isset($audit->new_values['u_id'])) {
                    $uId = $audit->new_values['u_id'];
                } elseif (is_array($audit->old_values) && isset($audit->old_values['u_id'])) {
                    $uId = $audit->old_values['u_id'];
                } elseif ($modelId) {
                    // If still N/A, try to get from database
                    try {
                        $sale = DB::connection('tenant')->table('sales')->where('id', $modelId)->first();
                        if ($sale && isset($sale->u_id)) {
                            $uId = $sale->u_id;
                        }
                    } catch (\Exception $e) {
                        // If database query fails, keep N/A
                    }
                }
                
                $changes = $this->getChangedValues($audit->old_values, $audit->new_values);
                if (!empty($changes)) {
                    return "Sale invoice #{$uId} was updated: " . $changes;
                }
                return "Sale invoice #{$uId} was updated";
            } elseif ($modelName === 'Purchase') {
                // For updates, try to get u_id from new_values first, then old_values, then use purchaseId as fallback
                $uId = 'N/A';
                if (is_array($audit->new_values) && isset($audit->new_values['u_id'])) {
                    $uId = $audit->new_values['u_id'];
                } elseif (is_array($audit->old_values) && isset($audit->old_values['u_id'])) {
                    $uId = $audit->old_values['u_id'];
                } elseif ($modelId) {
                    // If still N/A, try to get from database
                    try {
                        $purchase = DB::connection('tenant')->table('purchases')->where('id', $modelId)->first();
                        if ($purchase && isset($purchase->u_id)) {
                            $uId = $purchase->u_id;
                        }
                    } catch (\Exception $e) {
                        // If database query fails, keep N/A
                    }
                }
                
                $changes = $this->getChangedValues($audit->old_values, $audit->new_values);
                if (!empty($changes)) {
                    return "Purchase invoice #{$uId} was updated: " . $changes;
                }
                return "Purchase invoice #{$uId} was updated";
            } elseif ($modelName === 'DeJournal') {
                $changes = $this->getChangedValues($audit->old_values, $audit->new_values);
                if (!empty($changes)) {
                    return "Journal entry was updated: " . $changes;
                }
                return "Journal entry was updated";
            }
            
            $changes = $this->getChangedValues($audit->old_values, $audit->new_values);
            if (!empty($changes)) {
                return ucfirst($modelName) . " was updated: " . $changes;
            }
            return ucfirst($modelName) . " was updated";
        }
        
        if ($event === 'deleted') {
            if ($modelName === 'Sale') {
                $uId = is_array($audit->old_values) && isset($audit->old_values['u_id']) ? $audit->old_values['u_id'] : 'N/A';
                return "Sale invoice #{$uId} was deleted";
            } elseif ($modelName === 'Purchase') {
                $uId = is_array($audit->old_values) && isset($audit->old_values['u_id']) ? $audit->old_values['u_id'] : 'N/A';
                return "Purchase invoice #{$uId} was deleted";
            } elseif ($modelName === 'DeJournal') {
                return "Journal entry was deleted";
            }
            return ucfirst($modelName) . " was deleted";
        }
        
        return "Activity recorded for " . ucfirst($modelName);
    }

    /**
     * Get human-readable description of changed values
     */
    private function getChangedValues($oldValues, $newValues)
    {
        if (empty($oldValues) || empty($newValues)) {
            return '';
        }

        $changes = [];
        
        foreach ($newValues as $field => $newValue) {
            if (isset($oldValues[$field])) {
                $oldValue = $oldValues[$field];
                
                // Skip if values are the same
                if ($oldValue === $newValue) {
                    continue;
                }
                
                // Format the field name
                $fieldName = $this->getFieldDisplayName($field);
                
                // Format the values
                $formattedOldValue = $this->formatValue($oldValue);
                $formattedNewValue = $this->formatValue($newValue);
                
                $changes[] = "{$fieldName}: {$formattedOldValue} → {$formattedNewValue}";
            }
        }
        
        return implode(', ', $changes);
    }

    /**
     * Get human-readable field name
     */
    private function getFieldDisplayName($field)
    {
        $fieldMap = [
            'u_id' => 'Invoice Number',
            'customer_id' => 'Customer',
            'store_id' => 'Store',
            'sale_date' => 'Sale Date',
            'payment_status' => 'Payment Status',
            'total_amount' => 'Total Amount',
            'tax_amount' => 'Tax Amount',
            'discount_amount' => 'Discount Amount',
            'description' => 'Description',
            'note' => 'Note',
            'amount' => 'Amount',
            'transaction_type' => 'Transaction Type',
            'date' => 'Date',
            'journalable_type' => 'Related Model',
            'journalable_id' => 'Related ID',
            'debit_transaction_id' => 'Debit Transaction',
            'credit_transaction_id' => 'Credit Transaction',
            'status' => 'Status',
            'name' => 'Name',
            'title' => 'Title',
            'email' => 'Email',
            'phone' => 'Phone',
            'address' => 'Address'
        ];
        
        return $fieldMap[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    /**
     * Format value for display
     */
    private function formatValue($value)
    {
        if (is_null($value)) {
            return 'null';
        }
        
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        
        if (is_numeric($value)) {
            // Check if it's a currency amount
            if (strpos($value, '.') !== false || $value > 1000) {
                return number_format($value, 2);
            }
            return $value;
        }
        
        if (is_string($value)) {
            // Truncate long strings
            if (strlen($value) > 50) {
                return substr($value, 0, 50) . '...';
            }
            return $value;
        }
        
        if (is_array($value)) {
            return '[' . implode(', ', array_slice($value, 0, 3)) . ']';
        }
        
        return (string) $value;
    }
}
