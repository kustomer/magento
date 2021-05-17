## Kustomer Magento Extension


### Event Listeners

This extension works by listening to a number of Magento events via the Observer classes in `/Observer`.  The observers then publish events to the `kustomer_event` table defined in `/Setup/InstallSchema.php`.  


### Cron Jobs

Once every minute a cron job runs the `send` method in `/Model/Event.php` which is responsible for checking events in the database that have not been sent and attempting to send them to Kustomer.  If the request succeeds the `is_sent` column is marked to a 1 and the event will be excluded from future sends.  If the request fails we will iterate `send_count` until we hit a max number of attempts. Requests are sent to the `magento-api` service in Kustomer.  

Once a day we will also run a `clean` method in `/Model/Event.php` which will delete any events that have already been sent or have hit the max number of attempts.  This will keep the database table from growing overly large. 

