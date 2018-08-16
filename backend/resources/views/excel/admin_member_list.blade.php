<html>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email Address</th>
                <th>Username</th>
                <th>Date Created</th>
                <th>Account Status</th>
                <th>Email Verification Status</th>
                <th>Role</th>
                <th>Register Platform</th>
                <th>IP Address</th>
                <!-- <th>PHP Wallet</th>
                <th>BTC Wallet</th>
                <th>ABA Wallet</th> -->
            </tr>
        </thead>
        <tbody>
            @foreach($list as $members)
            <tr>
                
                <td>{{ $members->id }}</td>
                <td>{{ $members->first_name }}</td>
                <td>{{ $members->last_name }}</td>
                <td>{{ $members->email }}</td>
                <td>{{ $members->username }}</td>
                <td>{{ $members->created_at }}</td>
                <td>{{ $members->status_account == 1 ? 'Activated' : 'Unactivated' }}</td>
                <td>{{ $members->verified_mail == 1 ? 'Activated' : 'Unactivated' }}</td>
                <td>{{ $members->is_admin == 1 ? 'Administrator' : 'Member' }}</td>
                <td>{{ ucwords(strtolower($members->platform)) }}</td>
                <td>{{ $members->create_ip_address }}</td>
                <!-- <td>{{ $members->PHP }}</td>
                <td>{{ $members->BTC }}</td>
                <td>{{ $members->ABA }}</td> -->
                
            </tr>
            @endforeach

        </tbody>
    </table>
</html>