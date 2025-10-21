# OLT Monitoring System

A comprehensive Laravel-based monitoring system for ZTE C320 OLT devices with real-time dashboard, SNMP polling, and management capabilities.

## Features

### 🔍 Real-time Monitoring
- **Live Dashboard**: Real-time monitoring of OLT devices and ONU status
- **Interactive Charts**: Historical data visualization using Chart.js
- **Auto-refresh**: Dashboard updates every 30 seconds automatically
- **Status Indicators**: Color-coded status badges for easy identification

### 📊 SNMP Data Collection
- **ZTE C320 Specific OIDs**: Configured for ZTE C320 OLT devices
- **Periodic Polling**: Automated SNMP polling with configurable intervals
- **Metric Storage**: Historical data storage for RX/TX power and status
- **Artisan Commands**: Easy-to-use command-line tools for manual polling

### 🚨 Alert System
- **Threshold Monitoring**: Configurable alerts for low RX power
- **Email Notifications**: Automatic email alerts for critical events
- **Status Alerts**: Notifications for ONU offline/DyingGasp events
- **Database Logging**: All alerts stored in database for audit

### 🔧 OLT Management
- **SSH/Telnet Support**: Remote management via SSH or Telnet
- **ONU Reboot**: Remote ONU reboot functionality
- **Custom Commands**: Execute custom CLI commands on OLT
- **Status Queries**: Get detailed ONU status information

## Installation

### Prerequisites
- PHP 8.1 or higher
- Composer
- MySQL/PostgreSQL
- SNMP enabled on OLT devices

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd MonitoringOLT
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed --class=OltSeeder
   ```

5. **Configure SNMP settings**
   Update your `.env` file with database and mail settings:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=olt_monitoring
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

   MAIL_MAILER=smtp
   MAIL_HOST=your_smtp_host
   MAIL_PORT=587
   MAIL_USERNAME=your_email
   MAIL_PASSWORD=your_password
   MAIL_ENCRYPTION=tls
   ```

6. **Start the application**
   ```bash
   php artisan serve
   ```

## Usage

### Dashboard Access
- Navigate to `http://localhost:8000/dashboard`
- View real-time OLT and ONU status
- Click on OLT devices to see connected ONUs
- Use chart buttons to view historical data

### SNMP Polling
- **Automatic polling**: Configure polling intervals in OLT settings
- **Manual polling**: Run `php artisan olt:poll`
- **Specific OLT**: Run `php artisan olt:poll --olt-id=1`

### OLT Management
- Click the management button (⚙️) next to any ONU
- Enter OLT credentials (username/password)
- Use available management functions:
  - **Reboot ONU**: Restart specific ONU
  - **Get Status**: Retrieve detailed ONU information
  - **Custom Commands**: Execute CLI commands

## Configuration

### OLT Device Setup
1. **Add OLT Device**:
   - Name: Descriptive name for the OLT
   - IP Address: OLT management IP
   - Community String: SNMP community (default: 'public')
   - SNMP Port: Usually 161
   - Polling Interval: Seconds between polls (default: 60)

2. **SNMP Configuration on OLT**:
   ```bash
   # Enable SNMP on ZTE C320
   configure terminal
   snmp-server enable
   snmp-server community public ro
   snmp-server host 192.168.1.100 version 2c public
   ```

### Alert Thresholds
Configure alert thresholds in `app/Services/AlertService.php`:
```php
private $thresholds = [
    'low_rx_power' => -28.0,    // dBm
    'critical_rx_power' => -30.0, // dBm
];
```

## ZTE C320 OIDs

The system uses specific OIDs for ZTE C320 devices:

| Metric | OID | Description |
|--------|-----|-------------|
| ONU Status | 1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.3 | ONU phase state |
| ONU Serial | 1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.2 | ONU serial number |
| RX Power | 1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.4 | Received optical power |
| TX Power | 1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.5 | Transmitted optical power |
| ONU Model | 1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.6 | ONU model information |
| ONU Vendor | 1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.7 | ONU vendor information |

## Status Codes

| Code | Status | Description |
|------|--------|-------------|
| 1 | LOS | Loss of Signal |
| 3 | Working | ONU is operational |
| 4 | DyingGasp | ONU is in dying gasp state |
| 6 | Offline | ONU is offline |

## API Endpoints

### Dashboard Data
- `GET /dashboard` - Main dashboard view
- `GET /api/olts` - List all OLT devices
- `GET /api/olts/{id}/onus` - Get ONUs for specific OLT

### Management
- `POST /api/olt/{id}/reboot-onu` - Reboot specific ONU
- `POST /api/olt/{id}/execute-command` - Execute custom command

## Troubleshooting

### Common Issues

1. **SNMP Connection Failed**
   - Verify OLT IP address and SNMP configuration
   - Check firewall settings
   - Ensure SNMP community string is correct

2. **Dashboard Not Loading**
   - Check database connection
   - Verify all migrations are run
   - Clear application cache: `php artisan cache:clear`

3. **Management Commands Failing**
   - Verify OLT credentials
   - Check SSH/Telnet connectivity
   - Ensure proper CLI access permissions

### Logs
- Application logs: `storage/logs/laravel.log`
- SNMP polling logs: Check for "SNMP polling" entries
- Alert logs: Check for "Alert sent" entries

## Development

### Adding New OIDs
1. Update `app/Services/SnmpService.php`
2. Add new OID to the `$oids` array
3. Update polling logic in `pollOnuData()` method

### Custom Alerts
1. Extend `AlertService` class
2. Add new alert types to `OnuAlertNotification`
3. Update dashboard to display new alerts

## Security Considerations

- Change default SNMP community strings
- Use strong passwords for OLT management
- Implement proper user authentication
- Regular security updates
- Network segmentation for OLT management

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For support and questions:
- Create an issue in the repository
- Check the troubleshooting section
- Review Laravel and Livewire documentation

---

**Note**: This system is specifically designed for ZTE C320 OLT devices. For other OLT models, OIDs and CLI commands may need to be adjusted accordingly.