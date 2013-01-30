<?php

require_once '../lib/ably.php';
require_once 'factories/TestOption.php';

class ChannelPublishTest extends PHPUnit_Framework_TestCase {
    protected static $options;
    protected $ably;

    public static function setUpBeforeClass() {

        self::$options = TestOption::get_instance()->get_opts();

    }

    public static function tearDownAfterClass() {
        TestOption::get_instance()->clear_opts();
    }

    protected function setUp() {

        $options = self::$options;
        $defaults = array(
            'debug'     => false,
            'encrypted' => $options['encrypted'],
            'host'      => $options['host'],
            'key'       => $options['first_private_api_key'],
            'port'      => $options['port'],
        );

        $this->ably = new AblyRest( $defaults );
    }

    /**
     * Publish events with data of various datatypes
     */
    public function testPublishEventsWithVariousDataTypes() {
        echo '==testPublishEventsWithVariousDataTypes()';

        # first publish some messages
        $publish0 = $this->ably->channel('publish0');
        $publish0->publish("publish0", true);
        $publish0->publish("publish1", 24);
        $publish0->publish("publish2", 24.234);
        $publish0->publish("publish3", 'This is a string message payload');
        $publish0->publish("publish4", unpack('H*', 'This is a byte[] message payload')[1]);
        $publish0->publish("publish5", json_encode(array('test' => 'This is a JSONObject message payload')));
        $publish0->publish("publish6", json_encode(array('This is a JSONArray message payload')));

        # wait for history to be persisted
        sleep(10);

        # get the history for this channel
        $messages = $publish0->history();
        $this->assertNotNull( $messages, 'Expected non-null messages' );
        $this->assertEquals( 7, count($messages), 'Expected 7 messages' );

        $actual_message_order = array();

        # verify message contents
        foreach ($messages as $message) {
            array_push($actual_message_order, $message->name);
            switch ($message->name) {
                case 'publish0' : $this->assertEquals( true, $message->data,                                              'Expect publish0 to be Boolean(true)'       ); break;
                case 'publish1' : $this->assertEquals( 24, $message->data,                                                'Expect publish1 to be Integer(24)'         ); break;
                case 'publish2' : $this->assertEquals( 24.234, $message->data,                                            'Expect publish2 to be Float(24.234)'       ); break;
                case 'publish3' : $this->assertEquals( 'This is a string message payload', $message->data,                'Expect publish3 to be expected String'     ); break;
                case 'publish4' : $this->assertEquals( 'This is a byte[] message payload', pack('H*', $message->data),    'Expect publish4 to be expected byte[]'     ); break;
                case 'publish5' : $this->assertEquals( '{"test":"This is a JSONObject message payload"}', $message->data, 'Expect publish5 to be expected JSONObject' ); break;
                case 'publish6' : $this->assertEquals( '["This is a JSONArray message payload"]', $message->data,         'Expect publish6 to be expected JSONArray'  ); break;
            }
        }

        # verify message order
        $this->assertEquals( array('publish6', 'publish5', 'publish4', 'publish3', 'publish2', 'publish1', 'publish0'), $actual_message_order, 'Expect messages in reverse order' );
    }

}