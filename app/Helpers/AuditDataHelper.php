<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Str;

class AuditDataHelper
{
    /**
     * Convert JSON audit data to human-readable format
     */
    public static function formatAuditData($oldValues, $newValues, $event, $auditableType, $auditableId)
    {
        $oldData = is_string($oldValues) ? json_decode($oldValues, true) : $oldValues;
        $newData = is_string($newValues) ? json_decode($newValues, true) : $newValues;
        
        if ($event === 'created') {
            return self::formatCreatedEvent($newData, $auditableType, $auditableId);
        } elseif ($event === 'updated') {
            return self::formatUpdatedEvent($oldData, $newData, $auditableType, $auditableId);
        } elseif ($event === 'deleted') {
            return self::formatDeletedEvent($oldData, $auditableType, $auditableId);
        }
        
        return "Action performed on " . class_basename($auditableType);
    }
    
    /**
     * Format created event data
     */
    private static function formatCreatedEvent($data, $auditableType, $auditableId)
    {
        if (!$data || !is_array($data)) {
            return "New " . class_basename($auditableType) . " created";
        }
        
        $modelName = self::getModelDisplayName($auditableType);
        $description = self::buildDescription($data, $modelName);
        
        return $description;
    }
    
    /**
     * Format updated event data
     */
    private static function formatUpdatedEvent($oldData, $newData, $auditableType, $auditableId)
    {
        if (!$oldData || !$newData) {
            return class_basename($auditableType) . " updated";
        }
        
        $modelName = self::getModelDisplayName($auditableType);
        $changes = self::getChanges($oldData, $newData);
        
        if (empty($changes)) {
            return "No changes detected in " . ucfirst($modelName);
        }
        
        $changeDescriptions = [];
        foreach ($changes as $field => $change) {
            $changeDescriptions[] = self::formatFieldChange($field, $change);
        }
        
        return "Updated " . ucfirst($modelName) . ": " . implode(', ', $changeDescriptions);
    }
    
    /**
     * Format deleted event data
     */
    private static function formatDeletedEvent($data, $auditableType, $auditableId)
    {
        $modelName = self::getModelDisplayName($auditableType);
        
        if (!$data || !is_array($data)) {
            return $modelName . " was deleted";
        }
        
        $description = self::buildDeletedDescription($data, $modelName);
        return $description;
    }
    
    /**
     * Build description for deleted items
     */
    private static function buildDeletedDescription($data, $modelName)
    {
        $descriptions = [];
        
        // Handle common identification fields
        if (isset($data['name']) && !empty($data['name'])) {
            $descriptions[] = "named '" . $data['name'] . "'";
        }
        
        if (isset($data['title']) && !empty($data['title'])) {
            $descriptions[] = "titled '" . $data['title'] . "'";
        }
        
        if (isset($data['email']) && !empty($data['email'])) {
            $descriptions[] = "with email " . $data['email'];
        }
        
        if (isset($data['phone']) && !empty($data['phone'])) {
            $descriptions[] = "with phone " . $data['phone'];
        }
        
        // Handle financial data
        if (isset($data['amount'])) {
            $amount = number_format($data['amount']);
            $descriptions[] = "worth " . $amount;
        }
        
        if (isset($data['total_amount'])) {
            $amount = number_format($data['total_amount']);
            $descriptions[] = "worth " . $amount;
        }
        
        if (isset($data['paid_amount'])) {
            $amount = number_format($data['paid_amount']);
            $descriptions[] = "with paid amount " . $amount;
        }
        
        if (isset($data['due_amount'])) {
            $amount = number_format($data['due_amount']);
            $descriptions[] = "with due amount " . $amount;
        }
        
        // Handle dates
        if (isset($data['date'])) {
            $date = Carbon::parse($data['date'])->format('M d, Y');
            $descriptions[] = "dated " . $date;
        }
        
        if (isset($data['created_at'])) {
            $date = Carbon::parse($data['created_at'])->format('M d, Y');
            $descriptions[] = "created on " . $date;
        }
        
        // Handle transaction types
        if (isset($data['transaction_type'])) {
            $descriptions[] = "of type " . $data['transaction_type'];
        }
        
        // Handle payment status
        if (isset($data['payment_status'])) {
            $descriptions[] = "with payment status " . $data['payment_status'];
        }
        
        // Handle status (exclude for sales and purchases)
        if (isset($data['status']) && !in_array($modelName, ['sale', 'purchase'])) {
            $statusText = $data['status'] == 1 ? 'Active' : 'Inactive';
            $descriptions[] = "with status " . $statusText;
        }
        
        // Handle notes
        if (isset($data['note']) && !empty($data['note'])) {
            $descriptions[] = "with note: '" . $data['note'] . "'";
        }
        
        // Handle u_id for sales and purchases
        if (isset($data['u_id']) && !empty($data['u_id'])) {
            $descriptions[] = "Invoice/Bill #" . $data['u_id'];
        }
        
        // Build final description
        if (empty($descriptions)) {
            return ucfirst($modelName) . " was deleted";
        }
        
        return ucfirst($modelName) . " " . implode(', ', $descriptions) . " was deleted";
    }
    
    /**
     * Get model display name
     */
    private static function getModelDisplayName($auditableType)
    {
        $modelMap = [
            'App\\Models\\Sale' => 'sale',
            'App\\Models\\Purchase' => 'purchase',
            'App\\Models\\Customer' => 'customer',
            'App\\Models\\Supplier' => 'supplier',
            'App\\Models\\Product' => 'product',
            'App\\Models\\ProductStore' => 'product store',
            'App\\Models\\User' => 'user',
            'App\\Models\\Store' => 'store',
            'App\\Models\\Category' => 'category',
            'App\\Models\\Brand' => 'brand',
            'App\\Models\\Company' => 'company',
            'Hilinkz\\DEAccounting\\Models\\DeAccount' => 'account',
            'Hilinkz\\DEAccounting\\Models\\DeJournal' => 'journal entry',
            'Hilinkz\\DEAccounting\\Models\\DeAccountTransaction' => 'transaction',
            'Hilinkz\\DEAccounting\\Models\\DeBank' => 'bank',
            'Hilinkz\\DEAccounting\\Models\\DeBankAccount' => 'bank account',
        ];
        
        return $modelMap[$auditableType] ?? Str::lower(class_basename($auditableType));
    }
    
    /**
     * Build description from data
     */
    private static function buildDescription($data, $modelName)
    {
        $descriptions = [];
        
        // Handle common fields
        if (isset($data['date'])) {
            $date = Carbon::parse($data['date'])->format('M d, Y');
            $descriptions[] = "on " . $date;
        }
        
        if (isset($data['created_at'])) {
            $date = Carbon::parse($data['created_at'])->format('M d, Y');
            $descriptions[] = "on " . $date;
        }
        
        // Handle amount fields
        if (isset($data['amount'])) {
            $amount = number_format($data['amount']);
            $descriptions[] = "for " . $amount;
        }
        
        if (isset($data['total_amount'])) {
            $amount = number_format($data['total_amount']);
            $descriptions[] = "for " . $amount;
        }
        
        if (isset($data['paid_amount'])) {
            $amount = number_format($data['paid_amount']);
            $descriptions[] = "paid " . $amount;
        }
        
        if (isset($data['due_amount'])) {
            $amount = number_format($data['due_amount']);
            $descriptions[] = "due " . $amount;
        }
        
        // Handle transaction types
        if (isset($data['transaction_type'])) {
            $descriptions[] = "marked as " . $data['transaction_type'];
        }
        
        // Handle payment status
        if (isset($data['payment_status'])) {
            $descriptions[] = "status: " . $data['payment_status'];
        }
        
        // Handle names and titles
        if (isset($data['name'])) {
            $descriptions[] = "named '" . $data['name'] . "'";
        }
        
        if (isset($data['title'])) {
            $descriptions[] = "titled '" . $data['title'] . "'";
        }
        
        if (isset($data['email'])) {
            $descriptions[] = "with email " . $data['email'];
        }
        
        if (isset($data['phone'])) {
            $descriptions[] = "with phone " . $data['phone'];
        }
        
        // Handle notes
        if (isset($data['note']) && !empty($data['note'])) {
            $descriptions[] = "with note: '" . $data['note'] . "'";
        }
        
        // Handle u_id for sales and purchases
        if (isset($data['u_id']) && !empty($data['u_id'])) {
            $descriptions[] = "Invoice/Bill #" . $data['u_id'];
        }
        
        // Handle status (exclude for sales and purchases)
        if (isset($data['status']) && !in_array($modelName, ['sale', 'purchase'])) {
            $statusText = $data['status'] == 1 ? 'Active' : 'Inactive';
            $descriptions[] = "status set to " . $statusText;
        }
        
        // Build final description
        if (empty($descriptions)) {
            return "New " . ucfirst($modelName) . " created";
        }
        
        return "New " . ucfirst($modelName) . " " . implode(', ', $descriptions);
    }
    
    /**
     * Get changes between old and new data
     */
    private static function getChanges($oldData, $newData)
    {
        if (!$oldData || !$newData) {
            return [];
        }
        
        $changes = [];
        $allKeys = array_unique(array_merge(array_keys($oldData), array_keys($newData)));
        
        foreach ($allKeys as $key) {
            $oldValue = $oldData[$key] ?? null;
            $newValue = $newData[$key] ?? null;
            
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }
        
        return $changes;
    }
    
    /**
     * Format field change
     */
    private static function formatFieldChange($field, $change)
    {
        $fieldName = self::getFieldDisplayName($field);
        $oldValue = self::formatValue($change['old']);
        $newValue = self::formatValue($change['new']);
        
        // Special handling for status field
        if ($field === 'status') {
            $oldStatus = $change['old'] == 1 ? 'Active' : 'Inactive';
            $newStatus = $change['new'] == 1 ? 'Active' : 'Inactive';
            return "$fieldName changed from $oldStatus to $newStatus";
        }
        
        return "$fieldName changed from $oldValue to $newValue";
    }
    
    /**
     * Get field display name
     */
    private static function getFieldDisplayName($field)
    {
        $fieldMap = [
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'amount' => 'Amount',
            'total_amount' => 'Total Amount',
            'paid_amount' => 'Paid Amount',
            'due_amount' => 'Due Amount',
            'status' => 'Status',
            'payment_status' => 'Payment Status',
            'transaction_type' => 'Transaction Type',
            'note' => 'Note',
            'title' => 'Title',
            'address' => 'Address',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
        ];
        
        return $fieldMap[$field] ?? Str::title(str_replace('_', ' ', $field));
    }
    
    /**
     * Format value for display
     */
    private static function formatValue($value)
    {
        if (is_null($value)) {
            return 'empty';
        }
        
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        
        if (is_numeric($value)) {
            if (strpos($value, '.') !== false) {
                return number_format($value, 2);
            }
            return number_format($value);
        }
        
        if (is_string($value)) {
            if (empty($value)) {
                return 'empty';
            }
            if (strlen($value) > 50) {
                return "'" . substr($value, 0, 50) . "...'";
            }
            return "'$value'";
        }
        
        if (is_array($value)) {
            return 'array data';
        }
        
        return (string) $value;
    }
}
