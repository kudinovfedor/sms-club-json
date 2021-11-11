# SMS Club (JSON)

## Installation
Require this package with [composer](https://getcomposer.org) using the following command:
```bash
composer require kudinovfedor/sms-club-json
```

## Usage
```php
$manager = new \KudinovFedor\SmsClubJSON\SmsManager([
    'token' => 'token', // Token of the user account (which can be obtained in your account in the "Profile" section)
    'from'  => 'from' // Alpha name from which to send
]);

// or
$manager = new \KudinovFedor\SmsClubJSON\SmsManager();
$manager->setToken('token');
$manager->setFrom('from');
```

### Sending messages
After successful sending of the message to the partner’s system, an array will be returned, `info` in which the key is the id message in our system, by which you can receive the status and the phone number to which the message was sent.
In the example below, **106** requests for the given id sms, **380989361131** - recipient's number.
An array of `add_info` may also be present, where information on unsent messages is displayed in the form:

**Key** - recipient's number
**Value** - Text error
```php
$manager->setTo(['380989361131', '380989361130'])
$manager->setMessage('Your message');
$response = $manager->send();
```
return
```php
[
    "info": [
        "107": "380989361131"
    ],
    "add_info": [
        "380989361130": "Данный номер находится в черном списке"
    ]
]
```

### Getting the status of messages
Will return the array `info` in which the **key** will be `id` messages and the **value** of his `stat`
```php
$status = $manager->getStatus(['106', '107']);
// or
$manager->setSmsIds(['106', '107']);
$status = $manager->getStatus();
```
return
```php
[
    "106": "ENROUTE",
    "107": "REJECTD"
]
```

### Getting user balance
```php
$balance = $manager->getBalance();
```
return
```php
[
    "money": "8111.1700",
    "currency": "UAH"
]
```

### Getting a list of alpha usernames
```php
$originator = $manager->getOriginator();
```
return
```php
[
    [0] => "test1",
    [1] => "test2"
]
```

#### Check on errors
```php
if ($manager->hasErrors()) {
    $response = $manager->getErrors();
}
```

#### License
The SMS Club API is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT)
