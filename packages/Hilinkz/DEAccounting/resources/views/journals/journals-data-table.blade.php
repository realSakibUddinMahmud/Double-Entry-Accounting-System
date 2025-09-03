<style>
    table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
        background-color: white;
    }

    th {
        background-color: #f3f3f3;
    }

    td, th {
        border: 1px solid #dddddd;
        text-align: center;
        padding: 4px;
        font-size: 12px;
    }

    .bg-1 {
        background-color: #e1e1e14f;
    }

    .bg-2 {
        background-color: #f3f3f3;
    }

    .bold {
        font-weight: bold;
    }
</style>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Reference</th>
            <th>Account Title</th>
            <th>Note</th>
            <th>Debit (Tk)</th>
            <th>Credit (Tk)</th>
        </tr>
    </thead>
    <tbody>
        @php
            $last_date = null;
            $last_journalable = null;
            $last_debit_transaction_id = null;
            $last_credit_transaction_id = null;
            $total_debit_amount = 0;
            $total_credit_amount = 0;
            $subtotal_debit_amount = 0;
            $subtotal_credit_amount = 0;
        @endphp
        @forelse($journals as $index => $journal)
            @php
                $current_journalable = null;
                if ($journal->journalable_type && $journal->journalable_id) {
                    $current_journalable = $journal->journalable_type . '#' . $journal->journalable_id;
                }
            @endphp

            {{-- Show subtotal for previous journalable group when journalable changes --}}
            @if ($last_journalable && $last_journalable != $current_journalable && ($subtotal_debit_amount > 0 || $subtotal_credit_amount > 0))
                <tr class="" style="font-weight: bold;">
                    <td style="border-top:none; border-bottom: none;"></td>
                    <td class="bg-1"></td>
                    <td class="bg-1" colspan="2" style="text-align: right; padding-right: 10px;">Subtotal:</td>
                    <td class="bg-1" style="text-align: right;">{{ number_format($subtotal_debit_amount, 2) }}</td>
                    <td class="bg-1" style="text-align: right;">{{ number_format($subtotal_credit_amount, 2) }}</td>
                </tr>
                @php
                    $subtotal_debit_amount = 0;
                    $subtotal_credit_amount = 0;
                @endphp
            @endif

            <tr>
                @if ($last_date != date('d/m/Y', strtotime($journal->date)))
                    <td style="border-bottom: none;">{{ date('d/m/Y', strtotime($journal->date ?? null)) }}</td>
                @else
                    <td style="border-top:none; border-bottom: none;"></td>
                @endif

                @php
                    $current_journalable = null;
                    if ($journal->journalable_type && $journal->journalable_id) {
                        $current_journalable = $journal->journalable_type . '#' . $journal->journalable_id;
                    }
                @endphp

                @if ($last_journalable != $current_journalable)
                    <td style="border-bottom: none; text-align:left">
                        @if ($journal->journalable_type && $journal->journalable_id)
                            @php
                                // Try to get additional details
                                $invoiceNo = null;
                                $reference = null;
                                $displayName = null;
                                $displayId = $journal->journalable_id;
                                
                                try {
                                    if ($journal->journalable) {
                                        // For Sale and Purchase, use u_id
                                        if (in_array($journal->journalableAlias, ['Sale', 'Purchase'])) {
                                            if (isset($journal->journalable->u_id)) {
                                                $displayId = $journal->journalable->u_id;
                                            }
                                        } else {
                                            // For others (User, Customer, Supplier, etc.), use name
                                            if (isset($journal->journalable->name)) {
                                                $displayName = $journal->journalable->name;
                                            } elseif (isset($journal->journalable->first_name) && isset($journal->journalable->last_name)) {
                                                $displayName = $journal->journalable->first_name . ' ' . $journal->journalable->last_name;
                                            } elseif (isset($journal->journalable->title)) {
                                                $displayName = $journal->journalable->title;
                                            }
                                        }
                                        
                                        // Get invoice number and reference
                                        if (method_exists($journal->journalable, 'invoice_no') && isset($journal->journalable->invoice_no)) {
                                            $invoiceNo = $journal->journalable->invoice_no;
                                        }
                                        if (method_exists($journal->journalable, 'reference') && isset($journal->journalable->reference)) {
                                            $reference = $journal->journalable->reference;
                                        }
                                    }
                                } catch (Exception $e) {
                                    // Silently ignore relationship errors
                                }
                            @endphp
                            
                            @if ($displayName)
                                {{ $journal->journalableAlias }} - {{ $displayName }}
                            @else
                                {{ $journal->journalableAlias }} - {{ $displayId }}
                            @endif
                            
                            @if ($invoiceNo)
                                <br><small>Invoice: {{ $invoiceNo }}</small>
                            @endif
                            @if ($reference)
                                <br><small>Ref: {{ $reference }}</small>
                            @endif
                        @else
                            Manual Entry
                        @endif
                    </td>
                @else
                    <td style="border-top:none; border-bottom: none;"></td>
                @endif

                @if ($last_debit_transaction_id != $journal->debit_transaction_id)
                    <td style="border-bottom: none; text-align:left">
                        {{ $journal->debitTransaction->account->title ?? 'AC Missing' }}
                        {{-- @if ($journal->debitTransaction->account && $journal->debitTransaction->account->gas_station_id == null)
                            [Com]
                        @endif --}}
                        <span style="float:right;">(Dr.)</span><br>
                        {{ $journal->creditTransaction->account->title ?? null }}
                        {{-- @if ($journal->creditTransaction->account && $journal->creditTransaction->account->gas_station_id == null)
                            [Com]
                        @endif --}}
                        <span style="float:right;">(Cr.)</span>
                    </td>
                    <td style="border-bottom: none; text-align:left">{{ $journal->note ?? null }}</td>
                    <td style="border-bottom: none; text-align: right; vertical-align: top;">
                        {{ number_format($journal->debitTransaction->amount ?? null, 2) }}
                    </td>
                @else
                    <td style="border-top:none; border-bottom: none;">
                        {{ $journal->creditTransaction->account->title ?? null }}
                        {{-- @if ($journal->creditTransaction->account && $journal->creditTransaction->account->gas_station_id == null)
                            [Com]
                        @endif --}}
                        <span style="float:right;">(Cr.)</span>
                    </td>
                    <td style="border-top:none; border-bottom: none;"></td>
                    <td style="border-top:none; border-bottom: none;"></td>
                @endif
                @if ($last_debit_transaction_id != $journal->debit_transaction_id)
                    <td style="border-bottom: none; text-align: right; vertical-align: bottom;">
                        {{ number_format($journal->creditTransaction->amount ?? null, 2) }}
                    </td>
                @else
                    <td style="border-bottom: none; border-top: none; text-align: right; vertical-align: bottom;">
                        {{ number_format($journal->creditTransaction->amount ?? null, 2) }}
                    </td>
                @endif

                @php
                    if ($last_debit_transaction_id != $journal->debit_transaction_id) {
                        $total_debit_amount += $journal->debitTransaction->amount ?? 0;
                        $subtotal_debit_amount += $journal->debitTransaction->amount ?? 0;
                    }
                    if ($last_credit_transaction_id != $journal->credit_transaction_id) {
                        $total_credit_amount += $journal->creditTransaction->amount ?? 0;
                        $subtotal_credit_amount += $journal->creditTransaction->amount ?? 0;
                    }

                    $last_date = date('d/m/Y', strtotime($journal->date));
                    $last_journalable = $current_journalable;
                    $last_debit_transaction_id = $journal->debit_transaction_id;
                    $last_credit_transaction_id = $journal->credit_transaction_id;
                    $last_expense_type = $journal->note;

                @endphp
            </tr>
        @empty
            <tr>
                <td colspan="6">No records found</td>
            </tr>
        @endforelse
        
        {{-- Show subtotal for the last journalable group --}}
        @if ($last_journalable && ($subtotal_debit_amount > 0 || $subtotal_credit_amount > 0))
            <tr class="bg-1" style="font-weight: bold;">
                <td style="border-top:none; border-bottom: none;"></td>
                <td class="bg-1"></td>
                <td class="bg-1" colspan="2" style="text-align: right; padding-right: 10px;">Subtotal:</td>
                <td class="bg-1" style="text-align: right;">{{ number_format($subtotal_debit_amount, 2) }}</td>
                <td class="bg-1" style="text-align: right;">{{ number_format($subtotal_credit_amount, 2) }}</td>
            </tr>
        @endif
        <tr class="bg-2 bold">
            <td>Total</td>
            <td></td>
            <td></td>
            <td></td>
            <td style="text-align: right;">{{ number_format($total_debit_amount, 2) }}</td>
            <td style="text-align: right;">{{ number_format($total_credit_amount, 2) }}</td>
        </tr>
    </tbody>
</table>
<br>
