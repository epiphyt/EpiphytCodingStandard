# Epiphyt Coding Standard

The Epiphyt Coding Standard for PHP_CodeSniffer extends the WordPress Coding Standard by providing sniffs with additional checks.

It is more strict and leaves less room for custom code style. It is used throughout all Epiphyt products.

## Installation

The recommended way to install the Epiphyt Coding Standard is Composer.

```sh
composer require --dev epiphyt/coding-standard
```

## Configuration

Add the rule `EpiphytCodingStandard`, add all paths to the included sniffs, add a PHP `testVersion` as well as `minimum_supported_wp_version`:

```xml
<?xml version="1.0"?>
<ruleset name="My rule set">
	<rule ref="EpiphytCodingStandard"/>
	
	<config name="installed_paths" value="vendor/wp-coding-standards/wpcs,vendor/phpcsstandards/phpcsutils,vendor/phpcsstandards/phpcsextra,vendor/phpcompatibility/php-compatibility,vendor/phpcompatibility/phpcompatibility-paragonie,vendor/phpcompatibility/phpcompatibility-wp,vendor/slevomat/coding-standard,vendor/epiphyt/coding-standard" />
	<config name="testVersion" value="7.4-"/>
	<config name="minimum_supported_wp_version" value="6.3"/>
	
	<rule ref="WordPress.WP.I18n">
		<properties>
			<property name="text_domain" type="array">
				<element value="my-project-name"/>
			</property>
		</properties>
	</rule>
	
	<rule ref="WordPress.NamingConventions.PrefixAllGlobals">
		<properties>
			<property name="prefixes" type="array">
				<element value="my_project_name"/>
			</property>
		</properties>
	</rule>
</ruleset>
```
