<html>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <table>
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Career</th>
                <th>Status</th>
                <th>Date of Registration</th>
            </tr>
        </thead>
        <tbody>
            @foreach($list as $transaction)
            <tr>
                
            <td>{{ $transaction->first_name }} {{ $transaction->last_name }}</td>
                <td>{{ $transaction->email }}</td>
                <td>{{ $transaction->member_position_name }}</td>
                <td>{{ ucfirst($transaction->verified_mail != 1 ? 'Not Verified' : 'Verified') }}</td>
                <td>{{ date("M/d/Y H:i A", strtotime($transaction->created_at))}}</td>
                <!-- <td>{{ $transaction->PHP }}</td>
                <td>{{ $transaction->BTC }}</td>
                <td>{{ $transaction->ABA }}</td> -->
                
            </tr>
            @endforeach

        </tbody>
    </table>
</html>