<?php

require_once 'PEAR/PackageFileManager.php';

$version = '0.1.1';
$notes = <<<EOT
- added support for auto increment and primary key in schema.
- alterTable (Manager) now needs the full definition to work (use getTableFieldDefinition
 from Reverse module if you do not have a definition at hand) this eliminates the need
 of the declaration part in the alterTable array.
EOT;

$package = new PEAR_PackageFileManager();

$result = $package->setOptions(
    array(
        'packagefile'       => 'package_mysqli.xml',
        'package'           => 'MDB2_Driver_mysqli',
        'summary'           => 'mysqli MDB2 driver',
        'description'       => 'This is the MySQLi MDB2 driver.',
        'version'           => $version,
        'state'             => 'alpha',
        'license'           => 'BSD License',
        'filelistgenerator' => 'cvs',
        'include'           => array('*mysqli*'),
        'notes'             => $notes,
        'changelogoldtonew' => false,
        'simpleoutput'      => true,
        'baseinstalldir'    => '/',
        'packagedirectory'  => './',
        'dir_roles'         => array(
            'docs' => 'doc',
             'examples' => 'doc',
             'tests' => 'test',
             'tests/templates' => 'test',
        ),
    )
);

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}

$package->addMaintainer('lsmith', 'lead', 'Lukas Kahwe Smith', 'smith@backendmedia.com');

$package->addDependency('php', '5.0.0', 'ge', 'php', false);
$package->addDependency('PEAR', '1.0b1', 'ge', 'pkg', false);
$package->addDependency('MDB2', '2.0.0beta5', 'ge', 'pkg', false);
$package->addDependency('mysqli', null, 'has', 'ext', false);

$package->addglobalreplacement('package-info', '@package_version@', 'version');

if (isset($_GET['make']) || (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'make')) {
    $result = $package->writePackageFile();
} else {
    $result = $package->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
