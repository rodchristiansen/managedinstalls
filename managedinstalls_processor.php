<?php

use CFPropertyList\CFPropertyList;
use munkireport\processors\Processor;

class Managedinstalls_processor extends Processor
{
    public function run($plist)
    {
        $this->timestamp = date('Y-m-d H:i:s');

        if (!$plist) {
            throw new Exception(
                "Error Processing Request: No property list or data found",
                1
            );
        }

        if ($this->client_type === 'cimian') {
            $this->processCimianData($plist);
        } else {
            $this->processMunkiData($plist);
        }
    }

    private function processCimianData($json)
    {
        $data = json_decode($json, true);

        if (!isset($data['softwareInventory'])) {
            throw new Exception("Invalid Cimian data: missing softwareInventory", 1);
        }

        // Delete old entries
        Managedinstalls_model::where('serial_number', $this->serial_number)
            ->where('client_type', 'cimian')->delete();

        $save_array = [];

        foreach ($data['softwareInventory'] as $software) {
            $save_array[] = [
                'serial_number' => $this->serial_number,
                'name' => $software['name'],
                'version' => $software['version'],
                'status' => $software['status'],
                'install_date' => $this->convertWindowsDate($software['installDate']),
                'client_type' => 'cimian',
                'install_source' => $software['source'] ?? null,
            ];
        }

        Managedinstalls_model::insertChunked($save_array);
    }

    private function processMunkiData($plist)
    {
        $parser = new CFPropertyList();
        $parser->parse($plist, CFPropertyList::FORMAT_XML);
        if (!$mylist = $parser->toArray()) {
            return;
        }

        // Delete old entries
        Managedinstalls_model::where('serial_number', $this->serial_number)
            ->where('client_type', 'munki')->delete();

        $save_array = [];
        $new_installs = [];
        $uninstalls = [];

        foreach ($mylist as $name => $props) {
            $temp = [
                'serial_number' => $this->serial_number,
                'name' => $name,
                'version' => $props['installed_version'] ?? $props['version_to_install'] ?? '',
                'status' => $props['install_status'] ?? '',
                'install_date' => $props['installed'] ?? null,
                'client_type' => 'munki',
                'install_source' => null,
            ];

            $save_array[] = $temp;

            if ($temp['status'] === 'install_succeeded') {
                $new_installs[] = $temp;
            }

            if ($temp['status'] === 'uninstalled') {
                $uninstalls[] = $temp;
            }
        }

        Managedinstalls_model::insertChunked($save_array);

        $this->_storeEvents($new_installs, $uninstalls);
    }

    private function convertWindowsDate($windowsDate)
    {
        if (!$windowsDate) return null;

        $dt = DateTime::createFromFormat('Ymd', $windowsDate);

        return $dt ? $dt->format('Y-m-d H:i:s') : null;
    }

    private function _storeEvents($new_installs, $uninstalls)
    {
        if ($new_installs) {
            $count = count($new_installs);
            $msg = ['count' => $count];
            if ($count == 1) {
                $pkg = array_pop($new_installs);
                $msg['pkg'] = $pkg['name'] . ' ' . $pkg['version'];
            }
            $this->store_event(
                'success',
                'munki.package_installed',
                json_encode($msg)
            );
        } elseif ($uninstalls) {
            $count = count($uninstalls);
            $msg = ['count' => $count];
            if ($count == 1) {
                $pkg = array_pop($uninstalls);
                $msg['pkg'] = $pkg['name'] . ' ' . $pkg['version'];
            }
            $this->store_event(
                'success',
                'munki.package_uninstalled',
                json_encode($msg)
            );
        }
    }
}
