<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$version = 'YYY';
$state = 'stable';
$notes = <<<EOT
- added ability to escape wildcard characters in escape() and quote()
- added setTransactionIsolation()
- added savepoint support to beginTransaction(), commit() and rollback()
- added debug() call at the end of a query/prepare/execute calling (Request #7933)
- added context array parameter to debug() and make use of it whereever sensible
- added optional method name parameter to raiseError() and use whereever possible
- added ability to escape wildcard characters in escape() and quote()
- added debug() call at the end of a query/prepare/execute calling (Request #7933)
- added 'nativetype' output to tableInfo() and getTableFieldDefinition()
- added 'mdb2type' output to getTableFieldDefinition()
- reworked tableInfo() to use a common implementation based on getTableFieldDefinition()
  when a table name is passed (Bug #8124)
- fixed incorrect regex in mapNativeDatatype() (Bug #8256) (thx ioz at ionosfera dot com)
- use old DSN when rolling back open transactions in disconnect()
- MSSQL requires making columns exlicitly NULLable (Bug #8359)
- do not list empty contraints and indexes
- added support for autoincrement via IDENTITY in getDeclaration()
- ALTER TABLE bug when adding more than 1 column (Bug #8373)
- fixed handling return values when disable_query is set in _doQuery() and _execute()
- added dropIndex() to the manager module
- increased MDB2 dependency to XXX
- renamed valid_types property to valid_default_values in the Datatype module
- increased PHP dependency due to bug #31195
- using 'ADD COLUMN' syntax instead of just 'ADD' in alterTable()
- fixed bug #9024: typo in error checking
- fixed inheritance structure of convertResult()
- added support for new 'disable_iso_date' date DSN option (Request #8739)
- fix typos in error handling in a few places (bug #9024)
- do not skip id generation in nextId() when creating a sequence on demand
  because this prevents lastInsertID() from working
- added support for more error codes (patch by Adam Harvey)
- migrated to package.xml version 2
- implemented getTableFieldDefinition() in the Reverse module
- implemented getTableIndexDefinition() in the Reverse module
- implemented getTableConstraintDefinition() in the Reverse module
- implemented getTriggerDefinition() in the Reverse module
- implemented listTableConstraints() in the Manager module
- implemented listFunctions() in the Manager module
- implemented guid() in the Function module [globally unique identifier]
- implemented a fallback mechanism within getTableIndexDefinition() in the Reverse
  module to ignore the 'idxname_format' option and use the index name as provided
  in case of failure before returning an error
- added a 'nativetype_map_callback' option to map native data declarations back to
  custom data types (thanks to Andrew Hill).
- added missing integer data types and their length in _mapNativeDatatype()
- phpdoc fixes

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
$package->setPhpDep('4.3.0');
$package->setPearInstallerDep('1.4.0b1');
$package->addPackageDepWithChannel('required', 'MDB2', 'pear.php.net', 'XXX');
$package->addExtensionDep('required', 'mssql');

$package->addRelease();
$package->generateContents();
$package->setReleaseVersion($version);
$package->setAPIVersion('XXX');
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