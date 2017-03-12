<?php

namespace Laravel\Forge\Firewall;

use Laravel\Forge\ServerResources\ServerResource;

class FirewallRule extends ServerResource
{
    /**
     * Resource type.
     *
     * @return string
     */
    public static function resourceType()
    {
        return 'rule';
    }

    /**
     * Resource path (relative to Server URL).
     *
     * @return string
     */
    public function resourcePath()
    {
        return 'firewall-rules';
    }

    /**
     * Rule name.
     *
     * @return string|null
     */
    public function name()
    {
        return $this->getData('name');
    }

    /**
     * Rule port number.
     *
     * @return int
     */
    public function port()
    {
        return intval($this->getData('port', 0));
    }

    /**
     * Rule IP address.
     *
     * @return string|null
     */
    public function ipAddress()
    {
        return $this->getData('ip_address');
    }
}
