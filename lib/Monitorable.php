<?php

namespace SimpleSAML\Module\cirrusmonitor;

interface Monitorable
{
    /**
     * TODO define what can be returned
     * @return mixed
     */
    public function performCheck();
}
