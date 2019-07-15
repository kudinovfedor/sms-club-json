<?php

namespace KudinovFedor\SmsClubJSON;

/**
 * Class Manager
 */
abstract class Manager implements ManagerInterface
{
    /**
     * Sends a request to the API
     *
     * @param string $method
     * @param array $params
     * @return array
     */
    abstract protected function request(string $method, array $params = []): array;
}
