<?php

namespace KudinovFedor\SmsClubJSON;

use Exception;

/**
 * Class SmsManager
 * @author Kudinov Fedor <admin@joompress.biz>
 * @license https://opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/kudinovfedor/sms-club-json
 */
class SmsManager extends Manager
{
    /**
     * Service Address
     */
    const API_URL = 'https://im.smsclub.mobi/sms/';

    /**
     * The limit is not more than 100.
     */
    const LIMIT = 100;

    /**
     * Token of the user account
     * (which can be obtained in your account in the "Profile" section)
     *
     * @var string
     */
    private $token;

    /**
     * Alpha name from which to send
     *
     * @var string
     */
    public $from;

    /**
     * Array of numbers
     * (you can send up to 100 numbers per request)
     * @var array
     */
    public $to;

    /**
     * Message text
     *
     * @var string
     */
    public $message;

    /**
     * Array of Message IDs
     *
     * @var array
     */
    public $smsIds = [];

    /**
     * Fill when using the referral system (Optional)
     *
     * @var int
     */
    public $integrationId;

    /**
     * Array of statuses
     *
     * @var array
     */
    private $statuses = [
        'ENROUTE' => 'Message sent.',
        'DELIVRD' => 'Message delivered.',
        'EXPIRED' => 'Life expired, message not delivered.',
        'UNDELIV' => 'Unable to deliver message.',
        'REJECTD' => 'The message was rejected by system (black list or other filters).',
    ];

    /**
     * Errors
     *
     * @var array
     */
    private $errors = [];

    /**
     * There are errors
     *
     * @var bool
     */
    private $hasError = false;

    /**
     * SmsManager constructor.
     * @param array $params
     * @throws Exception
     */
    public function __construct(array $params = [])
    {
        if (isset($params['token'])) {
            $this->token = $this->setToken($params['token']);
        };

        if (isset($params['from'])) {
            $this->from = $this->setFrom($params['from']);
        };
    }

    /**
     * Sends a request to the API
     *
     * @param string $method
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function request(string $method, array $params = []): array
    {
        if (empty($this->token)) {
            $this->errors[] = 'Token is required!';
            $this->hasError = true;

            throw new Exception('Token is required!', 0);
        }

        try {
            $ch = curl_init();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            $this->hasError = true;

            return [
                'response' => 0,
                'error' => $e->getMessage(),
            ];
        }

        $data = is_array($params) ? json_encode($params) : $params;

        curl_setopt($ch, CURLOPT_URL, self::API_URL . $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
        ]);

        $json = curl_exec($ch);

        if ($json === false) {
            $this->errors[] = curl_error($ch);
            $this->hasError = true;

            return [
                'response' => null,
                'error' => curl_error($ch),
            ];
        }

        curl_close($ch);

        $response = json_decode($json, true);

        if (isset($response['success_request'])) {
            $response = $response['success_request'];
        }

        return $response;
    }

    /**
     * Sending messages
     *
     * @return array
     * @throws Exception
     */
    public function send(): array
    {
        $requiredProperties = ['from', 'to', 'message'];

        if (count($this->to) > self::LIMIT) {
            $this->errors[] = 'send function: The limit is not more than 100 of ID numbers';
            $this->hasError = true;

            throw new Exception('send function: The limit is not more than 100 of ID numbers', 0);
        }

        foreach ($requiredProperties as $property) {
            if (empty($this[$property])) {
                $this->errors[] = "Property: {$property} is not set!";
                $this->hasError = true;

                throw new Exception("Property: {$property} is not set!", 0);
            }
        }

        $params = [
            'src_addr' => $this->from,
            'phone' => $this->to,
            'message' => $this->message,
        ];

        if ($this->integrationId) {
            $params['integration_id'] = $this->integrationId;
        }

        $response = $this->request('send', $params);

        return $response;
    }

    /**
     * Getting the status of messages
     *
     * @param array $smsIds Array of Message IDs
     * @return array
     * @throws Exception
     */
    public function getStatus(array $smsIds = []): array
    {
        if (count($smsIds) > self::LIMIT) {
            $this->errors[] = 'getStatus function: The limit is not more than 100 of ID messages';
            $this->hasError = true;

            throw new Exception('getStatus function: The limit is not more than 100 of ID messages', 0);
        }

        if (!empty($smsIds)) {
            $this->setSmsIds($smsIds);
        }

        $params = [
            'id_sms' => $this->smsIds
        ];

        $response = $this->request('status', $params);

        if (isset($response['info'])) {
            $response = $response['info'];
        }

        $this->smsIds = [];

        return $response;
    }

    /**
     * Getting user balance
     *
     * @return array
     * @throws Exception
     */
    public function getBalance(): array
    {
        $response = $this->request('balance');

        if (isset($response['info'])) {
            $response = $response['info'];
        }

        return $response;
    }

    /**
     * Getting a list of alpha usernames
     *
     * @return array
     * @throws Exception
     */
    public function getOriginator(): array
    {
        $response = $this->request('originator');

        if (isset($response['info'])) {
            $response = $response['info'];
        }

        return $response;
    }

    /**
     * Check is errors exist
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->hasError;
    }

    /**
     * Get all errors
     *
     * @return array
     */
    public function gerErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken(string $token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @param string $from
     * @return $this
     * @throws Exception
     */
    public function setFrom(string $from)
    {
        if (!preg_match('@^[a-zA-Z0-9\s.]{1,11}$@', trim($from))) {
            $this->errors[] = 'It can contain Latin letters, numbers, spaces but no more than 11 characters';
            $this->hasError = true;

            throw new Exception('It can contain Latin letters, numbers, spaces but no more than 11 characters', 0);
        }

        $this->from = $from;

        return $this;
    }

    /**
     * @param array $to
     * @return $this
     */
    public function setTo(array $to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message)
    {
        $this->message = trim($message);

        return $this;
    }

    /**
     * @param int $integrationId
     * @return $this
     */
    public function setIntegrationId(int $integrationId)
    {
        $this->integrationId = $integrationId;

        return $this;
    }

    /**
     * @param array $smsIds
     * @return $this
     */
    public function setSmsIds(array $smsIds)
    {
        $this->smsIds = $smsIds;

        return $this;
    }
}
