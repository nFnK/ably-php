<?php
namespace Ably;

use Ably\Models\PaginatedResource;
use Ably\Models\Message;
use Ably\Exceptions\AblyException;

/**
 * Represents a channel
 * @property-read Presence $presence Presence object for this channel
 * @method publish(Message $message)
 * @method publish(string $name, string $data)
 */
class Channel {

    private $name;
    private $channelPath;
    private $ably;
    private $presence;
    public $options;

    private static $defaultOptions = array(
        'encrypted' => false,
        'cipherParams' => null,
    );

    /**
     * Constructor
     * @param AblyRest $ably Ably API instance
     * @param string $name Channel's name
     * @param array $options Channel options ['encrypted', 'cipherParams']
     * @throws AblyException
     */
    public function __construct( AblyRest $ably, $name, $options = array() ) {
        $this->ably = $ably;
        $this->name = $name;
        $this->channelPath = "/channels/" . urlencode( $name );
        $this->presence = new Presence( $ably, $this );

        $this->options = array_merge( self::$defaultOptions, $options );

        if ($this->options['encrypted'] && !$this->options['cipherParams']) {
            throw new AblyException( 'Channel created as encrypted, but no cipherParams provided' );
        }
    }

    /**
     * Magic getter for the $presence property
     */
    public function __get( $name ) {
        if ($name == 'presence') {
            return $this->presence;
        }

        throw new AblyException( 'Undefined property: '.__CLASS__.'::'.$name );
    }

    /**
     * Posts a message to this channel
     * @param mixed ... Either a Message or name and data
     * @param string $data Message data
     */
    public function publish() {

        $args = func_get_args();
        
        if (count($args) == 1 && is_a($args[0], 'Ably\Models\Message')) {
            $msg = $args[0];
        } else if (count($args) == 2) {
            $msg = new Message();
            $msg->name = $args[0];
            $msg->data = $args[1];
        } else {
            throw new AblyException( 'Wrong parameters provided, use either: Message or: name, data' );
        }

        if ($this->options['encrypted']) {
            $msg->setCipherParams( $this->options['cipherParams'] );
        }

        $this->ably->post( $this->channelPath . '/messages', $headers = array(), $msg->toJSON() );
        return true;
    }

    /**
     * Retrieves channel's history of messages
     * @param array $params Parameters to be sent with the request
     * @return PaginatedResource
     */
    public function history( $params = array() ) {
        return new PaginatedResource( $this->ably, 'Ably\Models\Message', $this->getCipherParams(), $this->getPath() . '/messages', $params );
    }

    /**
     * @return string Channel's name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string Channel portion of the request URI
     */
    public function getPath() {
        return $this->channelPath;
    }

    /**
     * @return CipherParams|null Cipher params if the channel is encrypted
     */
    public function getCipherParams() {
        return $this->options['encrypted'] ? $this->options['cipherParams'] : null;
    }
}