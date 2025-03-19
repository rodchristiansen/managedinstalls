<?php

/**
 * Managedinstalls Controller
 * Supports both Munki (macOS) and Cimian (Windows) data
 */
class Managedinstalls_controller extends Module_controller
{
    protected $module_path;
    protected $view_path;

    public function __construct()
    {
        $this->module_path = dirname(__FILE__);
        $this->view_path = $this->module_path . '/views/';
    }

    /**
     * Get managed installs by serial number
     */
    public function get_data($serial_number = '')
    {
        if (!$this->authorized()) {
            jsonView(['error' => 'Not authorized']);
        }

        $records = Managedinstalls_model::where('serial_number', $serial_number)
            ->filter()
            ->get()
            ->toArray();

        jsonView($records);
    }

    /**
     * Get pending installs (Munki or Cimian)
     */
    public function get_pending_installs($type = "munki")
    {
        jsonView($this->get_pending_items('pending_install', $type));
    }

    /**
     * Get pending removals (Munki or Cimian)
     */
    public function get_pending_removals($type = "munki")
    {
        jsonView($this->get_pending_items('pending_removal', $type));
    }

    /**
     * Common function to get pending items
     */
    private function get_pending_items($status, $type)
    {
        $hoursBack = 24 * 7;
        $fromdate = time() - 3600 * $hoursBack;

        $query = Managedinstalls_model::selectRaw('name, version, COUNT(*) as count')
            ->where('status', $status)
            ->where('client_type', $type)
            ->where('timestamp', '>', $fromdate)
            ->filter()
            ->groupBy('name', 'version')
            ->orderBy('count', 'desc');

        return $query->get()->toArray();
    }

    /**
     * Get package statistics
     */
    public function get_pkg_stats($pkg = '')
    {
        if (!$this->authorized()) {
            jsonView(['error' => 'Not authorized']);
        }

        $query = Managedinstalls_model::selectRaw('name, version, status, COUNT(*) as count')
            ->filter()
            ->groupBy('status', 'name', 'version')
            ->orderBy('version', 'desc');

        if ($pkg) {
            $query->where('name', $pkg);
        }

        $results = [];

        foreach ($query->get()->toArray() as $rs) {
            $status = ($rs['status'] == 'install_succeeded') ? 'installed' : $rs['status'];
            $key = $rs['name'] . $rs['version'];

            if (!isset($results[$key])) {
                $results[$key] = [
                    'name' => $rs['name'],
                    'version' => $rs['version'],
                    $status => $rs['count'],
                ];
            } else {
                $results[$key][$status] = ($results[$key][$status] ?? 0) + $rs['count'];
            }
        }

        jsonView(array_values($results));
    }

    /**
     * Retrieve installation statistics
     */
    public function get_stats($hours = 0)
    {
        if (!$this->authorized()) {
            jsonView(['error' => 'Not authorized']);
        }

        $timestamp = $hours > 0 ? time() - (60 * 60 * $hours) : 0;

        $stats = Managedinstalls_model::selectRaw('status, client_type, COUNT(DISTINCT serial_number) as clients, COUNT(status) as total_items')
            ->where('timestamp', '>', $timestamp)
            ->filter()
            ->groupBy('status', 'client_type')
            ->get()
            ->toArray();

        jsonView($stats);
    }

    /**
     * Client listing view
     */
    public function listing($name = '', $version = '')
    {
        if (!$this->authorized()) {
            redirect('auth/login');
        }

        $data['name'] = addslashes($name);
        $data['version'] = addslashes($version);
        $data['page'] = 'clients';
        $data['scripts'] = ["clients/client_list.js"];

        $obj = new View();
        $obj->view('managed_installs_listing', $data, $this->view_path);
    }

    /**
     * Display managed installs view
     */
    public function view($page)
    {
        if (!$this->authorized()) {
            redirect('auth/login');
        }

        $obj = new View();
        $obj->view($page, '', $this->view_path);
    }

    /**
     * Get client machines with specified install status
     */
    public function get_clients($status = 'pending_install', $hours = 24)
    {
        if (!$this->authorized()) {
            jsonView(['error' => 'Not authorized']);
        }

        $timestamp = time() - (60 * 60 * $hours);

        $clients = Managedinstalls_model::selectRaw('machine.computer_name, machine.serial_number, COUNT(*) as count')
            ->join('machine', 'machine.serial_number', '=', 'managedinstalls.serial_number')
            ->where('managedinstalls.status', $status)
            ->where('managedinstalls.timestamp', '>', $timestamp)
            ->filter()
            ->groupBy('machine.serial_number', 'machine.computer_name')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();

        jsonView($clients);
    }

    /**
     * Retrieve managed installs data
     */
    public function get_managed_installs()
    {
        $client_type = request('client_type', null);

        $query = Managedinstalls_model::query();

        if ($client_type) {
            $query->where('client_type', $client_type);
        }

        jsonView($query->get()->toArray());
    }
}
