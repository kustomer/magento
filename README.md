# Kustomer Integration for Magento 2

## 🔒 Security Notice

**CRITICAL SECURITY VULNERABILITIES IDENTIFIED**

This module's dependencies contain several high and critical severity security vulnerabilities. Please apply the security patches immediately.

### Quick Security Fix
```bash
# Run the automated security patch script
./apply-security-patches.sh
```

### Manual Security Updates
If you manage dependencies at the Magento root level:
```bash
# Update Composer first
composer self-update

# Update vulnerable packages to secure versions
composer require "symfony/process:^6.4.14"
composer require "zendframework/zend-http:^2.8.1"
composer require "zendframework/zend-diactoros:^1.8.4"
```

### Vulnerabilities Addressed
- **CVE-2022-24828**: Composer command injection (CRITICAL)
- **CVE-2021-29472**: Composer command injection via Mercurial (HIGH)
- **CVE-2024-51736**: Symfony Process command execution hijack (HIGH)
- **ZF2018-01**: Zend-HTTP URL rewrite vulnerability (HIGH)
- **ZF2018-01**: Zend-Diactoros URL rewrite vulnerability (MODERATE)

**📄 See [SECURITY_PATCH.md](SECURITY_PATCH.md) for detailed information.**

---

## Overview

This extension integrates Magento 2 with the Kustomer platform, enabling seamless customer service management.

## Installation

1. Install via Composer:
   ```bash
   composer require kustomer/kustomer-integration
   ```

2. Enable the module:
   ```bash
   php bin/magento module:enable Kustomer_KustomerIntegration
   php bin/magento setup:upgrade
   php bin/magento cache:clean
   ```

## Security Requirements

### Minimum Secure Package Versions
- `composer/composer`: ≥2.3.5
- `symfony/process`: ≥5.4.46 || ≥6.4.14 || ≥7.1.7
- `zendframework/zend-http`: ≥2.8.1
- `zendframework/zend-diactoros`: ≥1.8.4

### Security Best Practices
1. Keep all dependencies updated
2. Run `composer audit` regularly
3. Monitor security advisories
4. Filter suspicious HTTP headers at web server level

## Configuration

Configure the extension through:
- **Admin Panel**: Stores > Configuration > Services > Kustomer Integration
- **Command Line**: Use Magento CLI commands for bulk operations

## Development

### Requirements
- PHP 5.5+ || 7.0+ || 7.1+ || 7.2+ || 7.3+ || 7.4+
- Magento 2.x
- Secure versions of all dependencies (see security requirements above)

### Testing
```bash
# Run unit tests
phpunit Test/Unit/

# Run security audit
composer audit
```

## Support

For security issues, please:
1. Apply patches immediately using `./apply-security-patches.sh`
2. Contact your system administrator if patches cannot be applied
3. For Magento Cloud, coordinate with your hosting provider

For general support:
- Review the documentation in `DOCUMENTATION.md`
- Check the troubleshooting guide
- Contact Kustomer support

## License

Dual licensed under OSL-3.0 and AFL-3.0 licenses.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Apply security patches before development
4. Test thoroughly including security validation
5. Submit a pull request

---

**⚠️ SECURITY REMINDER**: Always apply security patches before deploying to production.

# Kustomer Extension for Adobe Commerce 2.x
This extension allows publication of Adobe Commerce events to Kustomer. It provides a `KustomerEventObserver` class that you can extend to publish events to your Kustomer account.

## Installation
1. `$ composer require kustomer/kustomer-integration` from your Adobe Commerce project root
2. `$ bin/magento module:enable Kustomer_KustomerIntegration --clear-static-content`
3. `$ bin/magento setup:upgrade`
4. `$ bin/magento setup:di:compile`

### Notes

#### Packagist

If you are using the default adobe commerce metapackage, you will need to add the packagist repo to your store's `composer.json`:
```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "https://repo.magento.com/"
        },
        {
            "type": "composer",
            "url": "https://packagist.org"
        }
    ]
}
```
Find more detailed instructions [here](http://devdocs.magento.com/guides/v2.2/comp-mgr/install-extensions.html)

#### Cron

The extension makes use of a cron job to collect events and send them to Kustomer and thus requires cron to be installed on the Adobe Commerce server.

You can check if the cron was installed correctly with `crontab -l`.  If the cron does not show up, run `bin/magento cron:install` in your Adobe Commerce dir. [This command](https://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-cron.html) is available with Adobe Commerce 2.2.

### Setup
Use of this extension requires an active Kustomer subscription. More information about Kustomer can be found on our [website](https://www.kustomer.com).

Before you can start sending data to Kustomer, you must install the Adobe Commerce app in your Kustomer account. You can do this by logging into the Kustomer app then go to `Settings>>App Directory` and click `Install` for Adobe Commerce.

Once Adobe Commerce is enabled on your Kustomer account, you need to create an API key so your Adobe Commerce store can send data to Kustomer. In the Kustomer app, go to `Settings>>API Keys`. Create a key here with the role `org.user` and keep a copy of the key handy. You will need it.

Next, go to your Adobe Commerce store's admin site and navigate to `Store>>Configuration>>Customers>>Kustomer` (if the `Kustomer` option is not available, see the installation instructions above). Select the store/website you want to integrate with Kustomer (or stay in the default scope if you want to use the integration globally). Enter the API key you generated in Kustomer into the API Key box and make sure the `Enabled` option is set to `Yes`. Next, select any of the default events you want to broadcast to Kustomer (such as when a new Customer is created) and click `Save`. You should be all set!

### Pausing the Extension
If you want to stop sending data to Kustomer, you can turn off the extension at any time by returning to the settings page and setting the `Enabled` value to `No`. Note that any data collected while the extension is off will not be sent to Kustomer.

## Custom Events and Objects
While the Kustomer Extension comes with a few pre-made events (new customers, orders, etc.), it is possible to send add your own custom events to Kustomer. To do this, create your own module and make sure `kustomer/kustomer-integration` is one of the requirements in `composer.json`. For more information about creating Adobe Commerce Modules, see the [Adobe Commerce Developer Documentation](http://devdocs.magento.com/guides/v2.2/extension-dev-guide/bk-extension-dev-guide.html).

Then create an Observer under `Observers/MySubscriptionObserver.php` and extend the `KustomerEventObserver` class. If you wanted to send a custom Subscription object to Kustomer when it is created, you might do something like this:

```php
<?php

namespace MyCompany\MyModule\Observer;

use Kustomer\KustomerIntegration\Observer\KustomerEventObserver;
use Magento\Framework\Event\Observer as EventObserver;

class MySubscriptionObserver extends KustomerEventObserver
{
    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $subscription = $observer->getEvent()->getSubscription();
        $customer = $subscription->getCustomerId();

        $type = 'subscription';
        $data = array(
            'plan' => $subscription->getPlan(),
            'term' => $subscription->getTerm(),
            'period_start' => $subscription->getPeriodStart(),
            'period_end' => $subscription->getPeriodEnd()
        );
        $this->publish($type, $data, $customer);
    }
}
```
Note that the `KustomerEventObserver.publish()` method requires a `$type` string, an array of `$data` (this is will be your object in Kustomer) and a `$customer` variable that is either a customer ID or an instance of `CustomerInterface`.

We also recommend you explicitly select which fields you send to Kustomer. This will avoid inadvertently sharing information you may not want to share with Kustomer, such as passwords.

Once your observer is ready, you need to register it in your module's `etc/events.xml` file. This example might look like this:

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
    <event name="subscription_create_success">
        <observer name="my_subscription_observer" instance="MyCompany\MyModule\Observer\MySubscriptionObserver" />
    </event>
</config>
```

Subscription objects will automatically be ingested by Kustomer and associated with your customer as Kustom Objects belonging to a dynamically-generated Klass.

## Customer Only Events
If you just want to send the Customer object, you can assign `$type` a value of `'customer'` and pass an empty array as `$data`:

```php
<?php
class MySubscriptionObserver extends KustomerEventObserver
{
    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        $type = 'customer';
        $data = [];
        $this->publish($type, $data, $customer);
    }
}
```
