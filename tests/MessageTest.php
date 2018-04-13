<?php

namespace Saritasa\Laravel\Controllers\Tests;

use Saritasa\Laravel\Controllers\Responses\Message;

class MessageTest extends TestCase
{
    public function testCreationMessage()
    {
        $text = str_random();
        $message = new Message($text);
        $this->assertEquals($text, $message->message);
    }
}
