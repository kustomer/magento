### Kustomer Integration for Magento Update History

#### 1.1.5

- Add required org name setting
- Adjusts base url (api.kustomerapp.com) to use subdomain (org-name.api.kustomerapp.com) to support new Kustomer regions


#### 1.1.4

- Add increment_id to event payload in addition to entity_id

#### 1.1.3

- Remove use of `\Magento\Framework\HTTP\Client\Curl`

#### 1.1.2

- Minor internal changes that should have no functional impact

#### 1.1.1

- Updates `composer.json` php dependency to `^7.1.0`

#### 1.1.0
- Updated order normalize function to coerce values to double-precise floats instead of strings
- Removed automatic insertion of store currency symbol as part of the above change
- Added this update history document