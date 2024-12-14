# Epiphyt Coding Standard

A strict coding standard used at Epiphyt.

## Requirements

This package requires Composer to be installed.

## Installation

Install Composer dependencies:

```sh
composer install
```

## Configuration

Configure the PHPCS path:

```sh
./vendor/bin/phpcs --config-set installed_paths /path/to/EpiphytCodingStandard
```

Don't forget to replace `/path/to/EpiphytCodingStandard` with the actual path to the coding standard.

Then, use it in your `.phpcs.xml`:

```xml
<?xml version="1.0"?>
<ruleset name="My rule set">
	<rule ref="EpiphytCodingStandard"/>
	
	<config name="testVersion" value="7.4-"/>
	<config name="minimum_supported_wp_version" value="6.3"/>
	
	<rule ref="WordPress.WP.I18n">
		<properties>
			<property name="text_domain" type="array" value="my-project-name"/>
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
