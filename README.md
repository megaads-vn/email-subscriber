# Email Subscriber

Run below command for install package on Laravel project

```
composer require megaads/email-subscriber
```

After installation completed. You need add `EmailSubscriberProvider` to ``config/app.php`` on section ``providers``. Like below: 

```
    Megaads\EmailSubscriber\Providers\EmailSubscriberProvider::class
```

Then, access url ``/email-subs/api/config`` for init file package config. Default username and password is api/ 123@123a. You can change username and password in ```config/subscriber.php```

