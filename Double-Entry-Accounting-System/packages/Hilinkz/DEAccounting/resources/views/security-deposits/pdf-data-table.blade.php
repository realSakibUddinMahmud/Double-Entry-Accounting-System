<style>
    table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
        background-color: white;
    }

    th {
        /* background-color: #F0F8FF; */
        background-color: #e1e1e15e;
    }

    td,
    th {
        border: 1px solid #dddddd;
        text-align: center;
        padding: 4px;
        font-size: 12px;
    }

    tr:nth-child(even) {
        /*background-color: whitesmoke;*/
    }

    .letter {
        background: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        padding: 10px;
        position: relative;
        max-width: 1080px;
    }

    .letter:before,
    .letter:after {
        content: "";
        height: 98%;
        position: absolute;
        width: 100%;
        z-index: -1;
    }

    .letter:before {
        background: #fafafa;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
        left: -5px;
        top: 4px;
        transform: rotate(-2.5deg);
    }

    .letter:after {
        background: #f6f6f6;
        box-shadow: 0 0 3px rgba(0, 0, 0, 0.2);
        right: -3px;
        top: 1px;
        transform: rotate(1.4deg);
    }

    .bg-1 {
        background-color: #9e9e9e57;
    }

    .bg-2 {
        background-color: #e1e1e15e;
    }

    .bold {
        font-weight: bold;
    }
</style>

<hr>
<table>
    <thead>
        <tr>
            <th>SL</th>
            <th class="text-center">Date</th>
            <th style="text-align:left">From</th>
            <th style="text-align:left">To</th>
            <th style="text-align:left">Note</th>
            <th style="text-align:right">Amount (Tk)</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total_amount = 0;
        @endphp
        @forelse($journals as $index => $journal)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-center">{{ date('d/m/Y', strtotime($journal->date)) }}</td>

                <td>
                    <p style="text-align:left">
                        Title: {{ $journal->creditTransaction->account->title ?? 'N/A' }}<br>
                        @if (!empty($journal->creditTransaction->account->account_no))
                            No: {{ $journal->creditTransaction->account->account_no }}<br>
                        @endif
                        {{ class_basename($journal->creditTransaction->account->accountable_alias ?? null) }}
                        -
                        {{ $journal->creditTransaction->account->accountable->name ?? ($journal->creditTransaction->account->accountable->title ?? 'N/A') }}
                    </p>
                </td>

                {{-- Account To --}}
                <td>
                    <p style="text-align:left">
                        Title: {{ $journal->debitTransaction->account->title ?? 'N/A' }}<br>
                        @if (!empty($journal->debitTransaction->account->account_no))
                            No: {{ $journal->debitTransaction->account->account_no }}<br>
                        @endif
                        {{ class_basename($journal->debitTransaction->account->accountable_alias ?? null) }}
                        -
                        {{ $journal->debitTransaction->account->accountable->name ?? ($journal->debitTransaction->account->accountable->title ?? 'N/A') }}
                    </p>
                </td>

                <td style="text-align:left">{{ $journal->note ?? null }} </td>
                <td style="text-align:right">{{ number_format($journal->amount ?? 0, 2) }} </td>
                @php
                    $total_amount += $journal->amount;
                @endphp
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    No Data Available
                </td>
            </tr>
        @endforelse
        {{-- @php
            $total_amount = 0;
            $total_amount += $journal->amount;
        @endphp --}}
        <tr class="bg-2 bold">
            <td colspan="5" style="text-align:right">Total</td>
            <td style="text-align:right">{{ number_format($total_amount, 2) }}</td>
        </tr>
    </tbody>
</table>
<br>
