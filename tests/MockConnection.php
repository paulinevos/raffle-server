<?php

namespace Vos\RaffleServer;

use Ratchet\ConnectionInterface;

final class MockConnection implements ConnectionInterface
{
    public $last = array(
        'send'  => ''
    , 'close' => false
    );

    public $remoteAddress = '127.0.0.1';

    public function send($data) {
        $this->last[__FUNCTION__] = $data;
    }

    public function close() {
        $this->last[__FUNCTION__] = true;
    }
}