# FGV

## Overview

This module agregates everthing that is custom needed to the FGV environments

## Classes

fgv - Base class

fgv::webenv - Resposible to create the web environment

## Parameters

The name of the parameters mirror the name of the config variables in the php-fpm configuration file and the pool configuration file. However, be sure to replace periods with underscores, as puppet does not support parameter names with periods.

For example, if your pool configuration should set the `pm.status_path` option to "/mystatus", the `pm.max_requests` option to "900", and `chroot` to "/www", you would use the following parameters in your manifest:

```puppet
phpfpm::pool { 'mypool':
    chroot          => '/www',
    pm_status_path  => '/mystatus',
    pm_max_requests => 900,
}
```


