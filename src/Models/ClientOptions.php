<?php
namespace Ably\Models;

use Ably\Log;

/**
 * Client library options
 */
class ClientOptions extends AuthOptions {

    /**
     * @var true A boolean value, indicating whether or not a TLS (“SSL”) secure connection should be used.
     */
    public $tls = true;
    
    /**
     * @var string|null A client id, used for identifying this client for presence purposes.
     * The clientId can be any string. This option is primarily intended to be used in situations where the library is instanced with a key;
     * note that a clientId may also be implicit in a token used to instance the library; an error will be raised if a clientId specified here conflicts with the clientId implicit in the token.
     */
    //public $clientId; // should be in authoptions
    
    /**
     * integer a number controlling the verbosity of the output from 1 (minimum, errors only) to 4 (most verbose);
     * @see \Ably\Log
     */
    public $logLevel = Log::WARNING;

    /**
     * @var function|null a function to handle each line of log output. If handler is not specified, console.log is used.
     * Note that the log level and log handler have global scope in the library and will thus not act independently between library instances when multiple library instances are existing concurrently.
     * @see \Ably\Log
     */
    public $logHandler;

    /**
     * @var bool If true, msgpack is used for communication, otherwise JSON is used
     * msgpack is currently not supported because of lack of working msgpack libraries for PHP
     */
    public $useBinaryProtocol = false;

    /**
     * @var string|string[] alternate server domain or array of domains (for use with fallback servers)
     * For use in development environments only.
     */
    public $host;

    /**
     * @var integer connection timeout after which a next fallback host is used
     */
    public $hostTimeout = 10000;

    /**
     * @var string a class that should be used for making HTTP connections
     * For use in development environments only.
     */
    public $httpClass = 'Ably\Http';

    public function __construct( $options = array() ) {
        parent::__construct( $options );

        if (empty($this->host)) {
            $this->host = array(
                'rest.ably.io',
                'a.ably-realtime.com',
                'b.ably-realtime.com',
                'c.ably-realtime.com',
                'd.ably-realtime.com',
                'e.ably-realtime.com',
            );
        }
    }
}