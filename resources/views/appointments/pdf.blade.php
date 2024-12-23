<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Details</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
            color: #333;
        }
        .container {
            margin: 20px auto;
            padding: 20px;
            max-width: 800px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #3B5998; /* Professional blue */
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .details-table th, .details-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .details-table th {
            background-color: #3B5998;
            color: white;
            font-weight: bold;
        }
        .details-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .appointment-reason {
            margin-top: 20px;
            padding: 10px;
            background: #f1f1f1;
            border-left: 4px solid #4CAF50;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Appointment Details</h1>
            <p>Generated on {{ \Carbon\Carbon::now('Asia/Kathmandu')->format('d M Y, h:i A') }}</p>
        </div>
        <table class="details-table">
            <tr>
                <th>Patient Name</th>
                <td>{{ $appointment->patient->user->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Doctor Name</th>
                <td>{{ $appointment->doctor->user->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Appointment Date</th>
                <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</td>
            </tr>
            <tr>
                <th>Day</th>
                <td>{{ $appointment->day }}</td>
            </tr>
            <tr>
                <th>Start Time</th>
                <td>{{ \Carbon\Carbon::parse($appointment->start_time)->format('h:i A') }}</td>
            </tr>
            <tr>
                <th>End Time</th>
                <td>{{ \Carbon\Carbon::parse($appointment->end_time)->format('h:i A') }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td style="color: {{ $appointment->status === 'completed' ? 'green' : ($appointment->status === 'cancelled' ? 'red' : '#FF9800') }};">
                    {{ ucfirst($appointment->status) }}
                </td>
            </tr>
        </table>

        @if($appointment->appointment_reason)
        <div class="appointment-reason">
            <strong>Reason for Appointment:</strong>
            <p>{{ $appointment->appointment_reason }}</p>
        </div>
        @endif

        <div class="footer">
            <p>Thank you for booking your appointment with <strong>DocBook</strong>!</p>
            <p>For more information or assistance, visit <a href="https://docbook.com">docbook.com</a>.</p>
        </div>
    </div>
</body>
</html>
