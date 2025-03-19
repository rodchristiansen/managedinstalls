# Managedinstalls Module

This module supports software inventory reporting from both Munki (macOS) and Cimian (Windows) managed clients.

## Supported fields

| Field          | Type             | Description                          |
|----------------|------------------|--------------------------------------|
| serial_number  | varchar(255)     | Device identifier                    |
| name           | varchar(255)     | Package name                         |
| display_name   | varchar(255)     | User-friendly package name           |
| version        | varchar(78)      | Installed or pending version         |
| size           | int              | Package size (Munki only)            |
| status         | varchar(255)     | Installation status                  |
| install_date   | datetime         | Installation date                    |
| client_type    | varchar(255)     | Client type (munki/cimian)           |
| install_source | varchar(255)     | Source info (Cimian only)            |

## Client Data

- **Munki:** Reports via ManagedInstallReport.plist
- **Cimian:** Reports via managedinstalls.json
