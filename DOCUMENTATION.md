# Documentation

## Logging

The extension uses the standard logger that outputs to the directory `/var/log` in your Adobe Commerce project. Within this directory there should be the files `exception.log` and `system.log`.

[Log Exception Example](./Model/Event.php#L183)

> Because logs are stored on the file system it makes silent failures due to minconfigurations or exceptions more likely.

## Observers

[Example (Checkout Success)](./Observer/CheckoutSuccessActionObserver.php)

Events from Adobe Commerce are observed by the extension and sent to Kustomer via `http` request. The events are also persisted to the `mysql` database table `kustomer_events` within the magento schema.

[Event - Send Example](./Model/Event.php#L127)  
[Event Model - Send Example](./Model/ResourceModel/Event.php#L158)  
[kustomer_events table Schema](./Setup/installSchema.php)

### Supported Events

- checkout success
- order cancel
- customer address save
- customer register success
- customer save data object

## Debugging

When order/customer data is not making it from your Adobe Commerce instance to Kustomer:

1. Confirm the integration has been configured properly both in Adobe Commerce and Kustomer.
2. Confirm the events exist in the `kustomer_events` table of the `mysql` database.
3. Check both log files for Kustomer messages.
