<html>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Wallet Address</th>
                <th>Type Of Member</th>
                <th>Token Value</th>
                <th>Remarks</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($list as $transaction)
            <tr>
                
                <td>{{ $transaction->member_log_id }}</td>
                <td>{{ $transaction->first_name}} {{ $transaction->last_name }}</td>
                <td>{{ $transaction->member_address }}</td>
                <td>{{ $transaction->member_position_name}}</td>
                <td>{{ $transaction->log_amount}} </td>
                <td>{{ $transaction->log_message }}</td>
                <td>{{ date("M/d/Y", strtotime($transaction->log_time))}}</td>
                <!-- <td>{{ $transaction->PHP }}</td>
                <td>{{ $transaction->BTC }}</td>
                <td>{{ $transaction->ABA }}</td> -->
                
            </tr>
            @endforeach

        </tbody>
    </table>
</html>