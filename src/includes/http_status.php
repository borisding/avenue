<?php
// list of respective http status code/descriptions.
// reference: http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
return [
    // 1xx, Informational
    100     => 'Continue',
    101     => 'Switching Protocols',
    102     => 'Processing',
    
    // 2xx, Success
    200     => 'OK',
    201     => 'Created',
    202     => 'Accepted',
    203     => 'Non-Authoritative Information',
    204     => 'No Content',
    205     => 'Reset Content',
    206     => 'Partial Content',
    207     => 'Multi-Status',
    208     => 'Already Reported',
    
    // 3xx, Redirection
    300     => 'Multiple Choices',
    301     => 'Moved Permanently',
    302     => 'Found',
    303     => 'See Other',
    304     => 'Not Modified',
    305     => 'Use Proxy',
    306     => 'Switch Proxy',
    307     => 'Temporary Redirect',
    308     => 'Permanent Redirect',
    
    // 4xx, Client Error
    400     => 'Bad Request',
    401     => 'Unauthorized',
    402     => 'Payment Required',
    403     => 'Forbidden',
    404     => 'Not Found',
    405     => 'Method Not Allowed',
    406     => 'Not Acceptable',
    407     => 'Proxy Authentication Required',
    408     => 'Request Timeout',
    409     => 'Conflict',
    410     => 'Gone',
    411     => 'Length Required',
    412     => 'Precondition Failed',
    413     => 'Request Entity Too Large',
    414     => 'Request-URI Too Long',
    415     => 'Unsupported Media Type',
    416     => 'Requested Range Not Satisfiable',
    417     => 'Expectation Failed',
    422     => 'Unprocessable Entity',
    423     => 'Locked',
    440     => 'Login Timeout',
    
    // 5xx, Server Error
    500     => 'Internal Server Error',
    501     => 'Not Implemented',
    502     => 'Bad Gateway',
    503     => 'Service Unavailable',
    504     => 'Gateway Timeout',
    505     => 'HTTP Version Not Supported',
    522     => 'Connection timed out',
    524     => 'A timeout occurred'
];