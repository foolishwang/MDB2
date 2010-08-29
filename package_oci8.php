<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$version = '1.5.0b3';
$state = 'beta';
$notes = <<<EOT

note:
- please use the latest ext/oci8 version from pecl.php.net/oci8
 (binaries are available from snaps.php.net and pecl4win.php.net)
- by default this driver emulates the database concept other RDBMS have by 
  using the "database" option instead of "username" in the DSN as the username name.
  This behaviour can be disabled by setting the "emulate_database" option to false.
- the multi_query test failes because this is not supported by ext/oci8
- the null LOB test failes because this is not supported by Oracle

open todo items:
- enable use of read() for LOBs to read a LOB in chunks
- buffer LOBs when doing buffered queries
EOT;

$description = 'This is the Oracle OCI8 MDB2 driver.';
$packagefile = './package_oci8.xml';

$options = array(
    'filelistgenerator' => 'cvs',
    'changelogoldtonew' => false,
    'simpleoutput'      => true,
    'baseinstalldir'    => '/',
    'packagedirectory'  => './',
    'packagefile'       => $packagefile,
    'clearcontents'     => false,
    'include'           => array('*oci8*'),
    'ignore'            => array('package_oci8.php'),
);

$package = &PEAR_PackageFileManager2::importOptions($packagefile, $options);
$package->setPackageType('php');

$package->clearDeps();
$package->setPhpDep('5.3.0');
$package->setPearInstallerDep('1.9.1');
$package->addPackageDepWithChannel('required', 'MDB2', 'pear.php.net', '1.5.0b3');
$package->addExtensionDep('required', 'oci8');

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