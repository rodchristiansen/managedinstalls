<?php

use \Model;

class Managedinstalls_model extends Model
{
    protected $table = 'managedinstalls';
    protected $fillable = [
        'serial_number', 'name', 'version', 'status',
        'install_date', 'client_type', 'install_source'
    ];

    public function process($data)
    {
        $json = json_decode($data, true);

        if (isset($json['softwareInventory'])) {
            // Cimian payload
            foreach ($json['softwareInventory'] as $software) {
                Managedinstalls_model::updateOrCreate(
                    [
                        'serial_number' => $this->serial_number,
                        'name' => $software['name'],
                    ],
                    [
                        'version' => $software['version'],
                        'status' => $software['status'],
                        'install_date' => isset($software['installDate']) ? $this->convertWindowsDate($software['installDate']) : null,
                        'client_type' => 'cimian',
                        'install_source' => isset($software['installSource']) ? $software['installSource'] : null
                    ]
                );
            }
        } elseif (isset($json['managed_installs'])) {
            // Munki payload (existing)
            foreach ($json['managed_installs'] as $software) {
                Managedinstalls_model::updateOrCreate(
                    [
                        'serial_number' => $this->serial_number,
                        'name' => $software['name'],
                    ],
                    [
                        'version' => $software['version_to_install'],
                        'status' => $software['install_status'],
                        'install_date' => isset($software['installed']) ? $software['installed'] : null,
                        'client_type' => 'munki',
                        'install_source' => null
                    ]
                );
            }
        }
    }

    /**
     * Helper method to convert Windows install date to standard MySQL datetime
     * @param string $windowsDate
     * @return string|null
     */
    private function convertWindowsDate($windowsDate)
    {
        if (!$windowsDate) return null;

        $dt = DateTime::createFromFormat('Ymd', $windowsDate);

        return $dt ? $dt->format('Y-m-d H:i:s') : null;
    }

    /**
     * Retrieve records for the listing
     * @return array
     */
    public function retrieve_records()
    {
        return $this->select('serial_number', 'name', 'version', 'status', 'install_date', 'client_type', 'install_source')
            ->get()
            ->toArray();
    }
}
