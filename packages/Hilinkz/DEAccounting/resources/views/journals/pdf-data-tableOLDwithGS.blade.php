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

@if (request('gs_id') != 'NATIVE')
    @forelse($gas_stations as $gas_station)
        <center>
            <p class="bg-1" style="text-align: center">{{ $gas_station->name }}</p>
        </center>
        <hr>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Account Title</th>
                    <th style="min-width: 200px">Note</th>
                    <th>Debit (Tk)</th>
                    <th>Credit (Tk)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $last_date = null;
                    $last_debit_transaction_id = null;
                    $last_credit_transaction_id = null;
                    $total_debit_amount = 0;
                    $total_credit_amount = 0;
                @endphp
                @forelse($journals->where('gas_station_id', $gas_station->id) as $index => $journal)
                    <tr>
                        @if ($last_date != date('d/m/Y', strtotime($journal->date)))
                            <td style="border-bottom: none;">{{ date('d/m/Y', strtotime($journal->date ?? null)) }}</td>
                        @else
                            <td style="border-top:none; border-bottom: none;"></td>
                        @endif

                        @if ($last_debit_transaction_id != $journal->debit_transaction_id)
                            <td style="border-bottom: none;">
                                <table style="border-collapse: collapse;">
                                    <tr style="border: none;">
                                        <td style="width: 90%; text-align: center; padding: 0; border: none;">
                                            <p style="margin: 1px;">
                                                {{ $journal->debitTransaction->account->title ?? 'AC Missing' }}
                                                @if ($journal->debitTransaction->account && $journal->debitTransaction->account->gas_station_id == null)
                                                    [Com]
                                                @endif
                                            </p>
                                            <p style="margin: 1px;">
                                                {{ $journal->creditTransaction->account->title ?? null }}
                                                @if ($journal->creditTransaction->account && $journal->creditTransaction->account->gas_station_id == null)
                                                    [Com]
                                                @endif
                                            </p>
                                        </td>
                                        <td style="width: 10%; padding: 0; border: none;">
                                            <p style="margin: 1px;">(Dr.)</p>
                                            <p style="margin: 1px;">(Cr.)</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="border-bottom: none;">{{ $journal->note ?? null }}</td>
                            <td style="border-bottom: none;text-align: right;">
                                {{ number_format($journal->debitTransaction->amount ?? null, 2) }}
                            </td>
                        @else
                            <td style="border-top:none; border-bottom: none;">
                                {{ $journal->creditTransaction->account->title ?? null }}
                                @if ($journal->creditTransaction->account && $journal->creditTransaction->account->gas_station_id == null)
                                    [Com]
                                @endif
                                <span style="float:right;">(Cr.)</span>
                            </td>
                            <td style="border-top:none; border-bottom: none;"></td>
                            <td style="border-top:none; border-bottom: none;"></td>
                        @endif
                        @if ($last_debit_transaction_id != $journal->debit_transaction_id)
                            <td style="border-bottom: none;text-align: right;">
                                {{ number_format($journal->creditTransaction->amount ?? null, 2) }}
                            </td>
                        @else
                            <td style="border-bottom: none; border-top: none;text-align: right;">
                                {{ number_format($journal->creditTransaction->amount ?? null, 2) }}
                            </td>
                        @endif

                        @php
                            if ($last_debit_transaction_id != $journal->debit_transaction_id) {
                                $total_debit_amount += $journal->debitTransaction->amount ?? 0;
                            }
                            if ($last_credit_transaction_id != $journal->credit_transaction_id) {
                                $total_credit_amount += $journal->creditTransaction->amount ?? 0;
                            }

                            $last_date = date('d/m/Y', strtotime($journal->date));
                            $last_debit_transaction_id = $journal->debit_transaction_id;
                            $last_credit_transaction_id = $journal->credit_transaction_id;
                        @endphp
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No records found</td>
                    </tr>
                @endforelse
                <tr class="bg-2 bold">
                    <td>Total</td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right;">{{ number_format($total_debit_amount, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($total_credit_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
        <br>
    @empty
    @endforelse
@else
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Account Title</th>
                <th>Note</th>
                <th>Debit (Tk)</th>
                <th>Credit (Tk)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $last_date = null;
                $last_debit_transaction_id = null;
                $last_credit_transaction_id = null;
                $total_debit_amount = 0;
                $total_credit_amount = 0;
            @endphp
            @forelse($journals as $index => $journal)
                <tr>
                    @if ($last_date != date('d/m/Y', strtotime($journal->date)))
                        <td style="border-bottom: none;">{{ date('d/m/Y', strtotime($journal->date ?? null)) }}</td>
                    @else
                        <td style="border-top:none; border-bottom: none;"></td>
                    @endif

                    @if ($last_debit_transaction_id != $journal->debit_transaction_id)
                        <td style="border-bottom: none;">
                            {{ $journal->debitTransaction->account->title ?? 'AC Missing' }}
                            @if ($journal->debitTransaction->account && $journal->debitTransaction->account->gas_station_id == null)
                                [Com]
                            @endif
                            <span style="float:right;">(Dr.)</span><br>
                            {{ $journal->creditTransaction->account->title ?? null }}
                            @if ($journal->creditTransaction->account && $journal->creditTransaction->account->gas_station_id == null)
                                [Com]
                            @endif
                            <span style="float:right;">(Cr.)</span>
                        </td>
                        <td style="border-bottom: none;">{{ $journal->note ?? null }}</td>
                        <td style="border-bottom: none;text-align: right;">
                            {{ number_format($journal->debitTransaction->amount ?? null, 2) }}
                        </td>
                    @else
                        <td style="border-top:none; border-bottom: none;">
                            {{ $journal->creditTransaction->account->title ?? null }}
                            @if ($journal->creditTransaction->account && $journal->creditTransaction->account->gas_station_id == null)
                                [Com]
                            @endif
                            <span style="float:right;">(Cr.)</span>
                        </td>
                        <td style="border-top:none; border-bottom: none;"></td>
                        <td style="border-top:none; border-bottom: none;"></td>
                    @endif
                    @if ($last_debit_transaction_id != $journal->debit_transaction_id)
                        <td style="border-bottom: none;text-align: right;">
                            {{ number_format($journal->creditTransaction->amount ?? null, 2) }}
                        </td>
                    @else
                        <td style="border-bottom: none; border-top: none;text-align: right;">
                            {{ number_format($journal->creditTransaction->amount ?? null, 2) }}
                        </td>
                    @endif

                    @php
                        if ($last_debit_transaction_id != $journal->debit_transaction_id) {
                            $total_debit_amount += $journal->debitTransaction->amount ?? 0;
                        }
                        if ($last_credit_transaction_id != $journal->credit_transaction_id) {
                            $total_credit_amount += $journal->creditTransaction->amount ?? 0;
                        }

                        $last_date = date('d/m/Y', strtotime($journal->date));
                        $last_debit_transaction_id = $journal->debit_transaction_id;
                        $last_credit_transaction_id = $journal->credit_transaction_id;
                    @endphp
                </tr>
            @empty
                <tr>
                    <td colspan="5">No records found</td>
                </tr>
            @endforelse
            <tr class="bg-2 bold">
                <td>Total</td>
                <td></td>
                <td></td>
                <td style="text-align: right;">{{ number_format($total_debit_amount, 2) }}</td>
                <td style="text-align: right;">{{ number_format($total_credit_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>
    <br>
@endif