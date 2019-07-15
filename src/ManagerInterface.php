<?php

namespace KudinovFedor\SmsClubJSON;

/**
 * Interface ManagerInterface
 */
interface ManagerInterface
{
    /**
     * Sending messages
     *
     * @return array
     */
    public function send(): array;

    /**
     * Getting the status of messages
     *
     * @param array $smsIds Array of Message IDs
     * @return array
     */
    public function getStatus(array $smsIds): array;

    /**
     * Getting user balance
     *
     * @return array
     */
    public function getBalance(): array;
}
