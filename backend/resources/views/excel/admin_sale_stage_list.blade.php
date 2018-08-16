<html>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Requested By</th>
                <th>Bonus Received</th>
                <th>Details</th>
                <th>Method</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($list as $transaction)
            <tr>
                
                <td>{{ $transaction->member_log_id }}</td>
                <td>{{ $transaction->first_name }} {{ $transaction->last_name }}</td>
                <td>{{ $transaction->log_amount . ' LOK'}}</td>
                <td>{{ $transaction->log_message}} </td>
                <td>{{ $transaction->log_method }}</td>
                <td>{{ date("M/d/Y", strtotime($transaction->log_time))}}</td>
                <!-- <td>{{ $transaction->PHP }}</td>
                <td>{{ $transaction->BTC }}</td>
                <td>{{ $transaction->ABA }}</td> -->
                
            </tr>
            @endforeach

        </tbody>
    </table>
</html>