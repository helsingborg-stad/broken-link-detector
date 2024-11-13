# Broken Link Detector
The **Broken Link Detector** plugin identifies and, where possible, fixes broken links in both content and metadata. Key features include:

- Probing both internal and external URLs stored in content and metadata to verify their validity.
- Generating a list of broken links, viewable by editors in the admin panel.
- Allowing administrators to create a blacklist of domains that should be excluded from the link-checking process.
- Displaying a warning (via a tooltip) for images that cannot be fetched from blacklisted external domains.

# Cli documentation
Broken link detector does not rely on scheduled actions due to its resource intensive nature. Instead a set of cli actions is provided to maintain the link registry.

## Cli commands
All broken links cli commands are placed under  **broken-link-detector** prefix. To get a up to date index of all options please use the following command: 

```wp broken-link-detector --info``

### Install, Uninstall & Reinstall
This command allows you to install, reinstall or uninstall the database table required for broken link registry. 

```wp broken-link-detector database --[install, uninstall, reinstall]```

### Find links 
This command will scan your sites content and meta data for links, and register them in the link registry. The links will not show up in the summary, util they have been classified as broken. The flags in this command is optional, and will default to true.

```wp broken-link-detector find-links --meta=true --content=true```

### Classify Links 
This command will asses and classify each found link to check if the link is valid or not. This is a resource intensive action, therefore a limit can be applied to classify a subset of links. 

```wp broken-link-detector classify-links --limit=[NUMBER]```


# Filter Documentation

This document provides an overview of the available filters within the `BrokenLinkDetector\Config` class. All filters are prefixed with `BrokenLinkDetector/Config`.

## Filters

### `BrokenLinkDetector/Config/getDatabaseVersionKey`

**Description:**  
Filter the key used for the database version.

**Default Value:**  
`'broken_link_detector_db_version'`

### `BrokenLinkDetector/Config/getDatabaseVersion`

**Description:**  
Filter the current database version from the options table.

**Default Value:**  
`'2.0.0'`

### `BrokenLinkDetector/Config/getTableName`

**Description:**  
Filter the name of the table that stores broken links.

**Default Value:**  
`'broken_links_detector'`

### `BrokenLinkDetector/Config/getPluginUrl`

**Description:**  
Filter the plugin URL.

**Default Value:**  
The value provided during object construction for `pluginUrl`.

### `BrokenLinkDetector/Config/getPluginPath`

**Description:**  
Filter the plugin path.

**Default Value:**  
The value provided during object construction for `pluginPath`.

### `BrokenLinkDetector/Config/getPluginFieldsPath`

**Description:**  
Filter the path where fields are located.

**Default Value:**  
The plugin path appended with `source/fields`.

### `BrokenLinkDetector/Config/getTextDomain`

**Description:**  
Filter the text domain.

**Default Value:**  
`'broken-link-detector'`

### `BrokenLinkDetector/Config/linkUpdaterBannedPostTypes`

**Description:**  
Filter the post types where link repair (link updater) should not run.

**Default Value:**  
`['attachment', 'revision', 'acf', 'acf-field', 'acf-field-group']`

### `BrokenLinkDetector/Config/linkDetectBannedPostTypes`

**Description:**  
Filter the post types that should not be checked for broken links.

**Default Value:**  
`['attachment', 'revision', 'acf', 'acf-field', 'acf-field-group']`

### `BrokenLinkDetector/Config/linkDetectAllowedPostStatuses`

**Description:**  
Filter the post types that should not be checked for broken links based on status.

**Default Value:**  
`['publish', 'private', 'password']`

### `BrokenLinkDetector/Config/responseCodesConsideredBroken`

**Description:**  
Filter the response codes that are considered broken.

**Default Value:**  
`[400, 403, 404, 410, 500, 502, 503, 504]`

### `BrokenLinkDetector/Config/checkIfDnsRespondsBeforeProbingUrl`

**Description:**  
Filter to determine if DNS should respond before probing the URL.

**Default Value:**  
`true`

### `BrokenLinkDetector/Config/getMaxRedirects`

**Description:**  
Filter the number of redirects to follow.

**Default Value:**  
`5`

### `BrokenLinkDetector/Config/getTimeout`

**Description:**  
Filter the timeout for the request.

**Default Value:**  
`5`

### `BrokenLinkDetector/Config/getRecheckInterval`

**Description:**  
Filter the interval for rechecking broken links in minutes.

**Default Value:**  
`5`

### `BrokenLinkDetector/Config/getDomainsThatShouldNotBeChecked`

**Description:**  
Filter the domains that should not be checked for broken links. These will be registered, but always return `null`.

**Default Value:**  
An empty array if no domains are set in ACF fields.

### `BrokenLinkDetector/Config/isContextCheckEnabled`

**Description:**  
Filter to enable/disable context check based on configuration and URL.

**Default Value:**  
`false`

### `BrokenLinkDetector/Config/getContextCheckUrl`

**Description:**  
Filter the URL to probe for the context check.

**Default Value:**  
The value from the ACF field or an empty string if not set.

### `BrokenLinkDetector/Config/getContextCheckTimeout`

**Description:**  
Filter the timeout in milliseconds for the context check.

**Default Value:**  
`3000`

### `BrokenLinkDetector/Config/getContextCheckDomainsToDisable`

**Description:**  
Filter the domains that should be disabled when the context check fails.

**Default Value:**  
The domains retrieved by the `getDomainsThatShouldNotBeChecked` method.

### `BrokenLinkDetector/Config/getContextCheckSuccessClass`

**Description:**  
Filter the class to be applied for a successful context check.

**Default Value:**  
`'context-check-avabile'`

### `BrokenLinkDetector/Config/getContextCheckFailedClass`

**Description:**  
Filter the class to be applied for a failed context check.

**Default Value:**  
`'context-check-unavabile'`

### `BrokenLinkDetector/Config/getContextCheckTooltipText`

**Description:**  
Filter the tooltip text for a disabled link due to context failure.

**Default Value:**  
The value from the ACF field or `'Link unavabile'` if not set.

### `BrokenLinkDetector/Config/getCommandNamespace`

**Description:**  
Filter the namespace for the WP CLI command.

**Default Value:**  
`'broken-link-detector'`

## Example Usage

To modify the filter for the database version key, you can use the following code in your plugin or theme:

```php
add_filter('BrokenLinkDetector/Config/getDatabaseVersionKey', function($versionKey) {
    return 'custom_version_key';
});
