<?php











namespace Composer;

use Composer\Autoload\ClassLoader;
use Composer\Semver\VersionParser;








class InstalledVersions
{
private static $installed = array (
  'root' => 
  array (
    'pretty_version' => '1.0.0+no-version-set',
    'version' => '1.0.0.0',
    'aliases' => 
    array (
    ),
    'reference' => NULL,
    'name' => 'topthink/think',
  ),
  'versions' => 
  array (
    'league/flysystem' => 
    array (
      'pretty_version' => '3.34.0',
      'version' => '3.34.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '2daaac3b0d4c83ea7ed5d8586e786f5d00f3540e',
    ),
    'league/flysystem-local' => 
    array (
      'pretty_version' => '3.31.0',
      'version' => '3.31.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '2f669db18a4c20c755c2bb7d3a7b0b2340488079',
    ),
    'league/mime-type-detection' => 
    array (
      'pretty_version' => '1.16.0',
      'version' => '1.16.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '2d6702ff215bf922936ccc1ad31007edc76451b9',
    ),
    'psr/container' => 
    array (
      'pretty_version' => '2.0.2',
      'version' => '2.0.2.0',
      'aliases' => 
      array (
      ),
      'reference' => 'c71ecc56dfe541dbd90c5360474fbc405f8d5963',
    ),
    'psr/http-message' => 
    array (
      'pretty_version' => '2.0',
      'version' => '2.0.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '402d35bcb92c70c026d1a6a9883f06b2ead23d71',
    ),
    'psr/log' => 
    array (
      'pretty_version' => '3.0.2',
      'version' => '3.0.2.0',
      'aliases' => 
      array (
      ),
      'reference' => 'f16e1d5863e37f8d8c2a01719f5b34baa2b714d3',
    ),
    'psr/simple-cache' => 
    array (
      'pretty_version' => '3.0.0',
      'version' => '3.0.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '764e0b3939f5ca87cb904f570ef9be2d78a07865',
    ),
    'symfony/polyfill-mbstring' => 
    array (
      'pretty_version' => 'v1.38.1',
      'version' => '1.38.1.0',
      'aliases' => 
      array (
      ),
      'reference' => '14c5439eec4ccff081ac14eca2dc57feb2a66d92',
    ),
    'symfony/var-dumper' => 
    array (
      'pretty_version' => 'v8.1.0',
      'version' => '8.1.0.0',
      'aliases' => 
      array (
      ),
      'reference' => 'c2c4df1d21477cc21c9f6dc1b14d07c3abc4963e',
    ),
    'topthink/framework' => 
    array (
      'pretty_version' => 'v8.1.4',
      'version' => '8.1.4.0',
      'aliases' => 
      array (
      ),
      'reference' => '8e7b2b2364047cbf71a38c4e397a9ca0d4ef2b01',
    ),
    'topthink/think' => 
    array (
      'pretty_version' => '1.0.0+no-version-set',
      'version' => '1.0.0.0',
      'aliases' => 
      array (
      ),
      'reference' => NULL,
    ),
    'topthink/think-captcha' => 
    array (
      'pretty_version' => 'v3.0.11',
      'version' => '3.0.11.0',
      'aliases' => 
      array (
      ),
      'reference' => '4f24f560a31011329e3d144732e5370d7676b3fb',
    ),
    'topthink/think-container' => 
    array (
      'pretty_version' => 'v3.0.2',
      'version' => '3.0.2.0',
      'aliases' => 
      array (
      ),
      'reference' => 'b2df244be1e7399ad4c8be1ccc40ed57868f730a',
    ),
    'topthink/think-dumper' => 
    array (
      'pretty_version' => 'v1.0.7',
      'version' => '1.0.7.0',
      'aliases' => 
      array (
      ),
      'reference' => '1bd79783bf9551330c7cf55c9ef49c82b3a2e110',
    ),
    'topthink/think-filesystem' => 
    array (
      'pretty_version' => 'v3.0.0',
      'version' => '3.0.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '7a1231a65bca278de9b7f9236767eef9741dfe5c',
    ),
    'topthink/think-helper' => 
    array (
      'pretty_version' => 'v3.1.12',
      'version' => '3.1.12.0',
      'aliases' => 
      array (
      ),
      'reference' => 'fe277121112a8f1c872e169a733ca80bb11c4acb',
    ),
    'topthink/think-multi-app' => 
    array (
      'pretty_version' => 'v1.1.1',
      'version' => '1.1.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'f93c604d5cfac2b613756273224ee2f88e457b88',
    ),
    'topthink/think-orm' => 
    array (
      'pretty_version' => 'v4.0.51',
      'version' => '4.0.51.0',
      'aliases' => 
      array (
      ),
      'reference' => '46abe2f824eb3bcb117d4c0ce93b203b592b79f7',
    ),
    'topthink/think-template' => 
    array (
      'pretty_version' => 'v3.0.2',
      'version' => '3.0.2.0',
      'aliases' => 
      array (
      ),
      'reference' => '0b88bd449f0f7626dd75b05f557c8bc208c08b0c',
    ),
    'topthink/think-trace' => 
    array (
      'pretty_version' => 'v2.0',
      'version' => '2.0.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '4ba6da2945b37931d61900a6e55dc02b05e5a63f',
    ),
    'topthink/think-validate' => 
    array (
      'pretty_version' => 'v3.0.7',
      'version' => '3.0.7.0',
      'aliases' => 
      array (
      ),
      'reference' => '85063f6d4ef8ed122f17a36179dc3e0949b30988',
    ),
    'topthink/think-view' => 
    array (
      'pretty_version' => 'v2.0.5',
      'version' => '2.0.5.0',
      'aliases' => 
      array (
      ),
      'reference' => 'b42009b98199b5a3833d3d6fd18c8a55aa511fad',
    ),
  ),
);
private static $canGetVendors;
private static $installedByVendor = array();







public static function getInstalledPackages()
{
$packages = array();
foreach (self::getInstalled() as $installed) {
$packages[] = array_keys($installed['versions']);
}

if (1 === \count($packages)) {
return $packages[0];
}

return array_keys(array_flip(\call_user_func_array('array_merge', $packages)));
}









public static function isInstalled($packageName)
{
foreach (self::getInstalled() as $installed) {
if (isset($installed['versions'][$packageName])) {
return true;
}
}

return false;
}














public static function satisfies(VersionParser $parser, $packageName, $constraint)
{
$constraint = $parser->parseConstraints($constraint);
$provided = $parser->parseConstraints(self::getVersionRanges($packageName));

return $provided->matches($constraint);
}










public static function getVersionRanges($packageName)
{
foreach (self::getInstalled() as $installed) {
if (!isset($installed['versions'][$packageName])) {
continue;
}

$ranges = array();
if (isset($installed['versions'][$packageName]['pretty_version'])) {
$ranges[] = $installed['versions'][$packageName]['pretty_version'];
}
if (array_key_exists('aliases', $installed['versions'][$packageName])) {
$ranges = array_merge($ranges, $installed['versions'][$packageName]['aliases']);
}
if (array_key_exists('replaced', $installed['versions'][$packageName])) {
$ranges = array_merge($ranges, $installed['versions'][$packageName]['replaced']);
}
if (array_key_exists('provided', $installed['versions'][$packageName])) {
$ranges = array_merge($ranges, $installed['versions'][$packageName]['provided']);
}

return implode(' || ', $ranges);
}

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}





public static function getVersion($packageName)
{
foreach (self::getInstalled() as $installed) {
if (!isset($installed['versions'][$packageName])) {
continue;
}

if (!isset($installed['versions'][$packageName]['version'])) {
return null;
}

return $installed['versions'][$packageName]['version'];
}

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}





public static function getPrettyVersion($packageName)
{
foreach (self::getInstalled() as $installed) {
if (!isset($installed['versions'][$packageName])) {
continue;
}

if (!isset($installed['versions'][$packageName]['pretty_version'])) {
return null;
}

return $installed['versions'][$packageName]['pretty_version'];
}

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}





public static function getReference($packageName)
{
foreach (self::getInstalled() as $installed) {
if (!isset($installed['versions'][$packageName])) {
continue;
}

if (!isset($installed['versions'][$packageName]['reference'])) {
return null;
}

return $installed['versions'][$packageName]['reference'];
}

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}





public static function getRootPackage()
{
$installed = self::getInstalled();

return $installed[0]['root'];
}








public static function getRawData()
{
@trigger_error('getRawData only returns the first dataset loaded, which may not be what you expect. Use getAllRawData() instead which returns all datasets for all autoloaders present in the process.', E_USER_DEPRECATED);

return self::$installed;
}







public static function getAllRawData()
{
return self::getInstalled();
}



















public static function reload($data)
{
self::$installed = $data;
self::$installedByVendor = array();
}





private static function getInstalled()
{
if (null === self::$canGetVendors) {
self::$canGetVendors = method_exists('Composer\Autoload\ClassLoader', 'getRegisteredLoaders');
}

$installed = array();

if (self::$canGetVendors) {
foreach (ClassLoader::getRegisteredLoaders() as $vendorDir => $loader) {
if (isset(self::$installedByVendor[$vendorDir])) {
$installed[] = self::$installedByVendor[$vendorDir];
} elseif (is_file($vendorDir.'/composer/installed.php')) {
$installed[] = self::$installedByVendor[$vendorDir] = require $vendorDir.'/composer/installed.php';
}
}
}

$installed[] = self::$installed;

return $installed;
}
}
