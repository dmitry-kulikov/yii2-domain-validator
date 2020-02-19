Change Log
==========

1.1.2 February 19, 2020
-----------------------

- Added possibility to run tests using Docker and documentation for this.

1.1.1 November 2, 2019
----------------------

- Bug: checkDNS = true leads to ErrorException for some bad domains on Alpine Linux.

1.1.0 June 11, 2019
-------------------

- Enh #5: Added the ability to extend the verification of DNS (maranqz).
- Bug: Fixed `kdn\yii2\validators\DomainValidator::$checkDNS` tells that every domain is correct on Alpine Linux.

1.0.3 March 7, 2018
-------------------

- Bug #4: Fixed error for $allowUnderscore = true in PHP 7.3.

1.0.2 February 19, 2017
-----------------------

- Bug #3: Fixed checkDNS returns false positives.
- Bug: Fixed typo in Russian translation.

1.0.1 July 15, 2016
-------------------

- Documentation.

1.0.0 July 7, 2016
------------------

- Initial release.
