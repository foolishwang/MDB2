<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$version = '1.5.0b3';
$state = 'beta';
$notes = <<<EOT
- Fixed bug #16612: Added the timestamp database attribute [genericbob]
- fixed bug #16118: escape doesn't take into account trailing backslashes [urkle]
- request #16903: Add ability to use ODBTP extension [hedroom]
- fixed numRows() with setLimit()

open todo items:
- explore fast limit/offset emulation (Request #4544)
EOT;

$description = 'This is the MS SQL Server MDB2 driver.';
$packagefile = './package_mssql.xml';

$options = array(
    'filelistgenerator' => 'cvs',
    'changelogoldtonew' => false,
    'simpleoutput'      => true,
    'baseinstalldir'    => '/',
    'packagedirectory'  => './',
    'packagefile'       => $packagefile,
    'clearcontents'     => false,
    'include'           => array('*mssql*'),
    'ignore'            => array('package_mssql.php'),
);

$package = &PEAR_PackageFileManager2::importOptions($packagefile, $options);
$package->setPackageType('php');

$package->clearDeps();
$package->setPhpDep('5.3.0');
$package->setPearInstallerDep('1.9.1');
$package->addPackageDepWithChannel('required', 'MDB2', 'pear.php.net', '2.5.0b3');
$package->addExtensionDep('required', 'mssql');

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