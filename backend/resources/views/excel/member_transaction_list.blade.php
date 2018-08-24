<html>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Requested</th>
                <th>Received</th>
                <th>Status</th>
                <th>Date</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($list as $transaction)
            <tr>
                
            <td>{{ $transaction->automatic_cash_in_id }}</td>
                <td>{{ $transaction->amount_requested . ' XS' }}</td>
                <td>{{ $transaction->log_status != "accepted" ? 'No data' : $transaction->log_amount . ' XS'}}</td>
                <td>{{ ucfirst($transaction->log_status) }}</td>
                <td>{{ date("M/d/Y", strtotime($transaction->log_time))}}</td>
                <td>{{ date("H:i A", strtotime($transaction->log_time))}}</td>
                <!-- <td>{{ $transaction->PHP }}</td>
                <td>{{ $transaction->BTC }}</td>
                <td>{{ $transaction->ABA }}</td> -->
                
            </tr>
            @endforeach

        </tbody>
    </table>
</html>