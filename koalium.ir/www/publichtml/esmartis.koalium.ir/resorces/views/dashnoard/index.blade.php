<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Enterprise Smart Home Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-3">
    <h1>Smart Home Dashboard</h1>
    <p>Alerts in the last 24 hours: {{ $alertsCount }}</p>
    <div id="chartContainer" style="height:300px;"></div>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Device ID</th>
          <th>Temperature (°C)</th>
          <th>Humidity (%)</th>
          <th>Door Locked</th>
          <th>Motion</th>
          <th>Fire</th>
          <th>Ambient</th>
          <th>Timestamp</th>
        </tr>
      </thead>
      <tbody>
      @foreach($logs as $log)
        <tr>
          <td>{{ $log->device_id }}</td>
          <td>{{ $log->temperature }}</td>
          <td>{{ $log->humidity }}</td>
          <td>{{ $log->door_locked ? 'Yes' : 'No' }}</td>
          <td>{{ $log->motion ? 'Yes' : 'No' }}</td>
          <td>{{ $log->fire ? 'Alert' : 'Normal' }}</td>
          <td>{{ json_encode($log->ambient) }}</td>
          <td>{{ $log->created_at }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
    {{ $logs->links() }}
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
  <script>
    // Example real-time integration using Pusher/Laravel Echo
    // Update the dashboard with new device logs.
    Pusher.logToConsole = true;
    var pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
      cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
    });
    var channel = pusher.subscribe("device-updates");
    channel.bind("App\\Events\\DeviceStatusUpdated", function(data) {
      console.log("Real-time update:", data);
      // You can add code to update the dashboard in real time.
    });
  </script>
</body>
</html>
