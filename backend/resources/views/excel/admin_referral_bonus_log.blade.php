<html>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Referrer</th>
                <th>Invitee</th>
                <th>Received Bonus</th>
                <th>Details</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @if($list != null)
                 @foreach($list as $transaction)
                 <tr>
                     
                     <td>{{ $transaction->referral_bonus_log_id }}</td>
                     <td>{{ $transaction["bonus_to"]->first_name }} {{ $transaction["bonus_to"]->last_name }}</td>
                     <td>{{ $transaction["bonus_from"]->first_name }} {{ $transaction["bonus_from"]->last_name }}</td>
                     <td>{{ $transaction->log_amount }} </td>
                     <td>{{ $transaction->log_message }}</td>
                     <td>{{ date("M/d/Y H:i A", strtotime($transaction->log_time))}}</td>
                     <!-- <td>{{ $transaction->PHP }}</td>
                     <td>{{ $transaction->BTC }}</td>
                     <td>{{ $transaction->ABA }}</td> -->
                     
                 </tr>
                 @endforeach
            @endif

        </tbody>
    </table>
</html>