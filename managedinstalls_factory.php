<?php

use \Model;

class Managedinstalls_model extends Model
{
    protected $table = 'managedinstalls';
    protected $fillable = [
        'serial_number', 'name', 'version', 'status',
        'install_date', 'client_type', 'install_source'
    ];

    /**
     * Main method to process incoming JSON data.
     *
     * @param string $serial_number
     * @param string $data
     * @throws Exception
     */
    public function process($serial_number, $data)
    {
        $json = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON decoding error: " . json_last_error_msg(), 1);
        }

        if (isset($json['softwareInventory'])) {
            $this->processCimianPayload($serial_number, $json['softwareInventory']);
        } elseif (isset($json['managed_installs'])) {
            $this->processMunkiPayload($serial_number, $json['managed_installs']);
        } else {
            throw new Exception("Unsupported payload structure", 1);
        }
    }

    private function processCimianPayload($serial_number, $softwareInventory)
    {
        foreach ($softwareInventory as $software) {
            Managedinstalls_model::updateOrCreate(
                [
                    'serial_number' => $serial_number,
                    'name' => $software['name'],
                    'client_type' => 'cimian',
                ],
                [
                    'version' => $software['version'],
                    'status' => $software['status'],
                    'install_date' => isset($software['installDate']) ? $this->convertWindowsDate($software['installDate']) : null,
                    'install_source' => $software['installSource'] ?? null
                ]
            );
        }
    }

    private function processMunkiPayload($serial_number, $managed_installs)
    {
        foreach ($managed_installs as $software) {
            Managedinstalls_model::updateOrCreate(
                [
                    'serial_number' => $serial_number,
                    'name' => $software['name'],
                    'client_type' => 'munki',
                ],
                [
                    'version' => $software['version_to_install'] ?? '',
                    'status' => $software['install_status'] ?? '',
                    'install_date' => $software['installed'] ?? null,
                    'install_source' => null
                ]
            );
        }
    }

    /**
     * Convert Windows date (YYYYMMDD) to MySQL datetime.
     *
     * @param string|null $windowsDate
     * @return string|null
     */
    private function convertWindowsDate($windowsDate)
    {
        if (!$windowsDate) return null;

        $dt = DateTime::createFromFormat('Ymd', $windowsDate);
        return $dt ? $dt->format('Y-m-d H:i:s') : null;
    }

    /**
     * Retrieve records for listing purposes.
     *
     * @return array
     */
    public function retrieve_records()
    {
        return $this->select(
            'serial_number', 'name', 'version', 
            'status', 'install_date', 'client_type', 'install_source'
        )->get()->toArray();
    }
}
