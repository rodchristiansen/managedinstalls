managedinstalls module
==============

Gathers information on what Munki has installed, removed, or processed. This module is paired with the [munkireport](https://github.com/munkireport/munkireport) module.


Table Schema
-----
* id - int(10) - ID of database row entry
* serial_number - varchar(255) - Serial number of Mac
* name - varchar(255) - Name of Munki item
* display_name - varchar(255) - Display name of Munki item
* version - varchar(78) - Version of Munki item
* size - int(10) - Size of Munki item in bytes
* installed - int(10) - 0/1 of it item is installed
* status - varchar(255) - Status of item when Munki last processed it
* type - varchar(255) - Type of Munki item, will be either `applesus` or `munki`
* munki_timestamp - big integer - When Munki processed the item