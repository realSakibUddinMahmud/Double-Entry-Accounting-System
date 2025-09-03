<style>
    table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
        background-color: white;
    }

    th {
        /* background-color: #F0F8FF; */
        background-color: #f3f3f3;
        /* background-color: #fc72401f; */
    }
    td{
        /* background-color: #e1e1e15e; */

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
        <tr >
            <th style="width: 30%;">Account Of</th>
            <th>{{ app('request')->input('account_title') }} - Ledgers</th>
        </tr>
    </thead>
    <tbody>
        @foreach($activeGasStations as $gs)
        <tr>
            <td style="width: 30%;">{{$gs->name??''}}</td>
            @if(!empty($ledgers[$gs->id]))
            <td >
                <table border="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Explanation AC Title</th>
                            <th>Note</th>
                            <th>Debit (Tk)</th>
                            <th>Credit (Tk)</th>
                            <th>Balance (Tk)</th>
                        </tr>
                    </thead>
                    @php
                        $last_date = null;
                        $total_debit_amount = 0;
                        if ($accountStatement[$gs->id] != null) {
                            $balance_amount = $accountStatement[$gs->id]->prev_balance ?? 0;
                            $total_credit_amount = $accountStatement[$gs->id]->prev_balance ?? 0;
                            $carry_balance = $accountStatement[$gs->id]->prev_balance ?? 0;
                        } else {
                            $balance_amount = 0;
                            $total_credit_amount = 0;
                            $carry_balance = 0;
                        }
                        if ($account_root_type == 1 || $account_root_type == 2) {
                            $balance_amount = $balance_amount;
                        } elseif ($account_root_type == 3 || $account_root_type == 4 || $account_root_type == 5) {
                            $balance_amount = -1 * $balance_amount;
                        }
                    @endphp
                    @if ($accountStatement[$gs->id] != null)
                        <tr>
                            <td> - </td>
                            <td><b>Opening Balance</b></td>
                            <td>Carry Forward</td>
                            <td> - </td>
                            <td> - </td>
                            <td style="text-align: right; ">{{ number_format(abs($balance_amount ?? 0),2) }} @if ($balance_amount < 0)
                                    Cr
                                @else
                                    Dr
                                @endif
                            </td>
                        </tr>
                    @endif
                    @forelse($ledgers[$gs->id] as $index => $ledger)
                       <tr>
                           @if ($last_date != date('d/m/Y', strtotime($ledger->date)))
                               <td style="border-bottom: none;">
                                   {{ date('d/m/Y', strtotime($ledger->date ?? null)) }}
                               </td>
                           @else
                               <td style="border-top:none; border-bottom: none;"></td>
                           @endif

                           <td style="text-align:left;">
                               {{ $ledger->title ?? null }}
                           </td>
                           <td style="font-family: solaimanlipi; text-align:left;">{{ $ledger->note ?? null }}</td>
                           <td style="text-align: right; ">{{ isset($ledger->debit) ? number_format($ledger->debit, 2) : '' }}</td>
                           <td style="text-align: right; ">{{ isset($ledger->credit) ? number_format($ledger->credit, 2) : '' }}</td>
                           

                           @php
                               $total_debit_amount += $ledger->debit ?? 0;
                               $total_credit_amount += $ledger->credit ?? 0;
                               $balance_amount = $ledger->cbalance;
                               
                               $last_date = date('d/m/Y', strtotime($ledger->date));
                               $last_expense_type = $ledger->note;
                               
                           @endphp
                           <td style="text-align: right; ">{{ number_format($balance_amount ?? 0,2) }}
                            @if($ledger->debit >0)
                                Dr
                            @else
                                Cr
                            @endif

                           </td>
                       </tr>
                    @empty
                        <tr>
                            <td colspan="6">No records found</td>
                        </tr>
                    @endforelse
                    <tr class="bg-2 bold">
                        <td>Total</td>
                        <td></td>
                        <td></td>
                        <td style="text-align: right; ">{{ number_format($total_debit_amount,2) }}</td>
                        <td style="text-align: right; ">{{ number_format($total_credit_amount-$carry_balance,2) }}</td>
                        <td style="text-align: right; ">{{ number_format($balance_amount ?? 0,2) }} @if ($balance_amount < 0)
                                Cr
                            @else
                                Dr
                            @endif
                        </td>
                    </tr>
                </table>
                
            </td>
            @else
            <td>
                -
            </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>