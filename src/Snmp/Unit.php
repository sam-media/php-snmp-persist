<?php
namespace Snmp;

class Unit
{
    const STRING = 'STRING';
    const INTEGER = 'INTEGER';
    const INTEGER32 = 'INTEGER32';
    const OCTET_STRING = 'OCTET_STRING';
    const OBJECT_IDENTIFIER = 'OBJECT_IDENTIFIER';
    const IPADDRESS = 'IPADDRESS';
    const COUNTER = 'COUNTER';
    const COUNTER32 = 'COUNTER32';
    const GAUGE = 'GAUGE';
    const GAUGE32 = 'GAUGE32';
    const UNSIGNED32 = 'UNSIGNED32';
    const TIMETICKS = 'TIMETICKS';
    const OPAQUE = 'OPAQUE';
    const COUNTER64 = 'COUNTER64';
    const NOSUCHOBJECT = 'NOSUCHOBJECT';
    const NOSUCHINSTANCE = 'NOSUCHINSTANCE';
    const ENDOFMIBVIEW = 'ENDOFMIBVIEW';
}
