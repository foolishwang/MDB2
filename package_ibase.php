<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$version = '1.5.0b3';
$state = 'beta';
$notes = <<<EOT
- fixed regression in lastInserID()
- fixed bug #15971: typo in standaloneQuery() [elpaso]
- fixed bug #16350: typo in setTransactionIsolation() [nilya]
- fixed bug #16741: Manager::listTableIndexes and listTableContraints should check NULL or 0 [nilya]
- fixed bug #17676: use connection resource when fetching LOBs [renskiy]
EOT;

$description = 'This is the Interbase/Firebird MDB2 driver.';
$packagefile = './package_ibase.xml';

$options = array(
    'filelistgenerator' => 'cvs',
    'changelogoldtonew' => false,
    'simpleoutput'      => true,
    'baseinstalldir'    => '/',
    'packagedirectory'  => './',
    'packagefile'       => $packagefile,
    'clearcontents'     => false,
    'include'           => array('*ibase*'),
    'ignore'            => array('package_ibase.php'),
);

$package = &PEAR_PackageFileManager2::importOptions($packagefile, $options);
$package->setPackageType('php');

$package->clearDeps();
$package->setPhpDep('5.3.0');
$package->setPearInstallerDep('1.9.1');
$package->addPackageDepWithChannel('required', 'MDB2', 'pear.php.net', '2.5.0b3');
$package->addExtensionDep('required', 'interbase');

$package->addRelease();
$package->generateContents();
$package->setReleaseVersion($version);
$package->setAPIVersion($version);
$package->setReleaseStability($state);
$package->setAPIStability($state);
$package->setNotes($notes);
$package->setDescription($description);
$package->addGlobalReplacement('package-info', '@package_version@', 'version');

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
    $package->writePackageFile();
} else {
    $package->debugPackageFile();
}
