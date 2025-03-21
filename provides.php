<?php

return array(
    'listings' => array(
        'managed_installs' => array(
            'view' => 'managed_installs_listing',
            'i18n' => 'managedinstalls.title',
        ),
    ),
    'widgets' => array(
        'get_failing' => array('view' => 'get_failing_widget'),
        'pending_apple' => array('view' => 'pending_apple_widget'),
        'pending_munki' => array('view' => 'pending_munki_widget'),
        'pending_removals_munki' => array('view' => 'pending_removals_munki_widget'),
        'pending' => array('view' => 'pending_widget'),
    ),
    'reports' => array(
        array(
            'view' => 'pkg_stats',
            'i18n' => 'managedinstalls.installratio_report',
        ),
    ),
    'client_types' => array(
        'munki',
        'cimian',
    ),
);
