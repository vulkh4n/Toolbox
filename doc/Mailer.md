### Mailer:

Mailer allows you to replace **WordPress** default and aging mailing solution. Mailer **only supports SMTP DSN** out of the box. However, Mailer may also support **Amazon SES**, **Gmail**, **MailChimp**, **Mailgun**, **Mailjet**, **Postmark**, **SendGrid**, **Sendinblue** and **OhMySMTP** DSN by installing the optional related composer bundle.

---

#### Enabling Mailer with default SMTP DSN:

```yaml
# config/config.yaml
filter:
    mailer:
        dsn: "smtp://user:pass@smtp.example.com:25"
```

---

#### Enabling support for optional DSN:

Say you want to use **Amazon SES** to send e-mails. **You will need to make it available to mailer before use**. To do so, you will have to require it into composer by using the following command `php composer.phar require symfony/amazon-mailer`.

Then, and only then you will be able to use **Amazon SES** DSN by setting the `dsn` config variable to the following value:

```yaml
# config/config.yaml
filter:
    mailer:
        dsn: "ses+smtp://USERNAME:PASSWORD@default"
```

##### List of all the composer bundles by service:

| Service    | Install with                                            |
|------------|---------------------------------------------------------|
| Amazon SES | `php composer.phar require symfony/amazon-mailer`       |
| Gmail      | `php composer.phar require symfony/google-mailer`       |
| MailChimp  | `php composer.phar require symfony/mailchimp-mailer`    |
| Mailgun    | `php composer.phar require symfony/mailgun-mailer`      |
| Mailjet    | `php composer.phar require symfony/mailjet-mailer`      |
| Postmark   | `php composer.phar require symfony/postmark-mailer`     |
| SendGrid   | `php composer.phar require symfony/sendgrid-mailer`     |
| Sendinblue | `php composer.phar require symfony/sendinblue-mailer`   |
| OhMySMTP   | `php composer.phar require symfony/oh-my-smtp-mailer`   |

##### List of all the DSN format by service:

Each service comes with its own **DSN**, **HTTP**, and **API** DSN format.

| Service    | SMTP DSN Format                              | HTTP DSN Format                           | API DSN Format                              |
|------------|----------------------------------------------|-------------------------------------------|---------------------------------------------|
| Amazon SES | ses+smtp://USERNAME:PASSWORD@default         | ses+https://ACCESS_KEY:SECRET_KEY@default | ses+api://ACCESS_KEY:SECRET_KEY@default     |
| Gmail      | gmail+smtp://USERNAME:PASSWORD@default       | n/a                                       | n/a                                         |
| MailChimp  | mandrill+smtp://USERNAME:PASSWORD@default    | mandrill+https://KEY@default              | mandrill+api://KEY@default                  |
| Mailgun    | mailgun+smtp://USERNAME:PASSWORD@default     | mailgun+https://KEY:DOMAIN@default        | mailgun+api://KEY:DOMAIN@default            |
| Mailjet    | mailjet+smtp://ACCESS_KEY:SECRET_KEY@default | n/a                                       | mailjet+api://ACCESS_KEY:SECRET_KEY@default |
| Postmark   | postmark+smtp://ID@default                   | n/a                                       | postmark+api://KEY@default                  |
| SendGrid   | sendgrid+smtp://KEY@default                  | n/a                                       | sendgrid+api://KEY@default                  |
| Sendinblue | sendinblue+smtp://USERNAME:PASSWORD@default  | n/a                                       | sendinblue+api://KEY@default                |
| OhMySMTP   | ohmysmtp+smtp://API_TOKEN@default            | n/a                                       | ohmysmtp+api://API_TOKEN@default            |

---

##### From config variable:

The 'From' yaml config variable is very important. Some hosts will block outgoing mail from an address if it doesn't exist. Some hosts may also refuse to relay mail from an unknown domain.

Defining both the `name` and the `address` yaml config variables will be used as a fallback option by the mailer since most of the time neither the filters nor the headers are used.


**The 'From' priority will observe the following order**:

1. WordPress filters `wp_mail_from` and `wp_mail_from_name`.
2. Email header definition such as `From: Heisenberg <heisenberg@example.com>`
3. Config variables:

    ```yaml
   # config/config.yaml
    filter:
        mailer:
            from:
                name: "Heisenberg"
                address: "heisenberg@example.com"
    ```

4. Guessing from domain name: `From: WordPress <wordpress@mydomain.com>`
5. Default config variables: `From: WordPress <admin@wordpress.com>`.