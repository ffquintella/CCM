<?php

$result = dns_get_record("gcc._tcp.srvc.fgv.br", DNS_SRV);
print_r($result);