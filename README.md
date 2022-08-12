## I. About :

---

Toolbox is a collection of filters that bridge **WordPress** install to other applications such as **Discord**, **Orocrm**, **WPForms** and many more.
A simple tool to support modern **WordPress** basic use.

## II. Installation :

---

1. Install composer dependencies with `php composer.phar install`.
2. Make sure there is a **config/config.yaml**, if not, you must create a valid yaml config file.

## III. Configuration :

---

Each filter comes with it's very own set of yaml config variables. If you are not familiar with Yaml syntax, you may consider reading the following page: [The YAML Format](https://symfony.com/doc/5.4/components/yaml/yaml_format.html)

### Detailed configuration by filter :

1. [Google Analytics](doc/GoogleAnalytics.md)
2. [Mailer](doc/Mailer.md)

Say you want to enable both **Google Analytics** and **Mailer**, your **config/config.yaml** will look like the following: 

```yaml
# config/config.yaml
filter:
    google_analytics:
        tag: "MY-GTAG-ID"
    mailer:
        from:
            name: "Heinsenberg"
            address: "heisenberg@saymyname.com"
        dsn: "smtp://smtp.example.com:25"
```