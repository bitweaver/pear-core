--TEST--
PEAR_Registry->packageInfo() (API v1.1)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
require_once 'PEAR/Registry.php';
$pv = phpversion() . '';
$av = $pv{0} == '4' ? 'apiversion' : 'apiVersion';
if (!in_array($av, get_class_methods('PEAR_Registry'))) {
    echo 'skip';
}
if (PEAR_Registry::apiVersion() != '1.1') {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pf = new PEAR_PackageFile_v1;
$pf->setConfig($config);
$pf->setSummary('sum');
$pf->setDescription('desc');
$pf->setLicense('PHP License');
$pf->setVersion('1.0.0');
$pf->setState('stable');
$pf->setDate('2004-11-17');
$pf->setNotes('sum');
$pf->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$pf->addFile('', 'foo.php', array('role' => 'php'));
$pf->setPackage('foop');
$pf->addPackageDep('glob', '', 'has');
$ret = $reg->addPackage2($pf);
$phpunit->assertTrue($ret, 'install of valid package');

$pf2 = new PEAR_PackageFile_v2_rw;
$pf2->setConfig($config);
$pf2->setPackageType('extsrc');
$pf2->addBinarypackage('foo_win');
$pf2->setPackage('foo');
$pf2->setChannel('grob');
$pf2->setAPIStability('stable');
$pf2->setReleaseStability('stable');
$pf2->setAPIVersion('1.0.0');
$pf2->setReleaseVersion('1.0.0');
$pf2->setDate('2004-11-12');
$pf2->setDescription('foo source');
$pf2->setSummary('foo');
$pf2->setLicense('PHP License');
$pf2->setLogger($fakelog);
$pf2->clearContents();
$pf2->addFile('', 'foo.grop', array('role' => 'src'));
$pf2->addBinarypackage('foo_linux');
$pf2->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$pf2->setNotes('blah');
$pf2->setPearinstallerDep('1.4.0a1');
$pf2->setPhpDep('4.2.0', '5.0.0');
$pf2->addPackageDepWithChannel('optional', 'frong', 'floop');
$pf2->setProvidesExtension('foo');
$cf = new PEAR_ChannelFile;
$cf->setName('grob');
$cf->setServer('grob');
$cf->setSummary('grob');
$cf->setDefaultPEARProtocols();
$reg = &$config->getRegistry();
$ret = $reg->addChannel($cf);
$phpunit->assertTrue($ret, 'channel add');

$ret = $reg->addPackage2($pf2);
$phpunit->assertTrue($ret, 'valid pf2');

$ret = $reg->packageInfo($pf->getPackage());
$phpunit->assertTrue(isset($ret['_lastmodified']), 'lastmodified set');
unset($ret['_lastmodified']);
$phpunit->assertEquals($pf->getArray(), $ret, 'BC test, just for kicks');
$phpunit->assertEquals('stable', $reg->packageInfo('foop', 'release_state'), 'state');
$phpunit->assertEquals('1.0.0', $reg->packageInfo('foop', 'version'), 'version');
$phpunit->assertEquals('PHP License', $reg->packageInfo('foop', 'release_license'), 'release_license');
$phpunit->assertEquals('sum', $reg->packageInfo('foop', 'release_notes'), 'release_notes');
$phpunit->assertEquals(array(array(
'handle' => 'cellog',
'role' => 'lead',
'email' => 'cellog@php.net',
'name' => 'Greg Beaver')), $reg->packageInfo('foop', 'maintainers'), 'maintainers');
$phpunit->assertEquals(array(array(
'type' => 'pkg',
'name' => 'glob',
'rel' => 'has',
'optional' => 'no',
)), $reg->packageInfo('foop', 'release_deps'), 'deps');

$ret = $reg->packageInfo($pf2->getPackage(), null, $pf2->getChannel());
$phpunit->assertTrue(isset($ret['_lastmodified']), 'lastmodified set');
unset($ret['_lastmodified']);
$phpunit->assertEquals($pf2->getArray(true), $ret, 'pf2 basic packageInfo');
// tests for BC spoofing
$ret = $reg->packageInfo($pf2->getPackage(), 'release_state', $pf2->getChannel());
$phpunit->assertEquals('stable', $ret, 'state');
$ret = $reg->packageInfo($pf2->getPackage(), 'version', $pf2->getChannel());
$phpunit->assertEquals('1.0.0', $ret, 'version');
$ret = $reg->packageInfo($pf2->getPackage(), 'release_license', $pf2->getChannel());
$phpunit->assertEquals('PHP License', $ret, 'licenes');
$ret = $reg->packageInfo($pf2->getPackage(), 'release_notes', $pf2->getChannel());
$phpunit->assertEquals('blah', $ret, 'notes');
$ret = $reg->packageInfo($pf2->getPackage(), 'maintainers', $pf2->getChannel());
$phpunit->assertEquals(array(
array(
'name' => 'Greg Beaver',
'email' => 'cellog@php.net',
'active' => 'yes',
'handle' => 'cellog',
'role' => 'lead')
), $ret, 'maintainers');
$ret = $reg->packageInfo($pf2->getPackage(), 'release_deps', $pf2->getChannel());
$phpunit->assertEquals(array (
  0 =>
  array (
    'type' => 'php',
    'rel' => 'le',
    'version' => '5.0.0',
    'optional' => 'no',
  ),
  1 =>
  array (
    'type' => 'php',
    'rel' => 'ge',
    'version' => '4.2.0',
    'optional' => 'no',
  ),
  2 =>
  array (
    'type' => 'pkg',
    'channel' => 'pear.php.net',
    'name' => 'PEAR',
    'rel' => 'ge',
    'version' => '1.4.0a1',
    'optional' => 'no',
  ),
  3 =>
  array (
    'type' => 'pkg',
    'channel' => 'floop',
    'name' => 'frong',
    'rel' => 'has',
    'optional' => 'yes',
  ),
), $ret, 'deps');
$ret = $reg->packageInfo();
unset($ret[0]['_lastmodified']);
$phpunit->assertEquals(array($pf->getArray()), $ret, 'default');
$ret = $reg->packageInfo(null, null, null);
unset($ret['grob'][0]['_lastmodified']);
unset($ret['pear.php.net'][0]['_lastmodified']);
ksort($ret);
$phpunit->assertEquals(array(
'__uri' => array(),
'doc.php.net' => array(),
'grob' => array($pf2->getArray(true)),
'pear.php.net' => array($pf->getArray()),
'pecl.php.net' => array()), $ret, 'default whole shebang');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
