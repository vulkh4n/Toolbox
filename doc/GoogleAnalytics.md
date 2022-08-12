## Google Analytics :

---

**Google Analytics** is a simple filter adding the Google tag into the header of your **WordPress** installation allowing **Google Analytics** to properly track user activity on **WordPress** front-office.

#### Enabling google analytics :

To enable **Google Analytics support**, you must provide the `tag` config variable with a **valid Google Analytics tag** so it properly records to your property.

You may also define the `hook_name` config variable with a **valid WordPress hook** to trigger it on a different fashion. If the config variable is omitted, it will fall back to `wp_head` default value.

#### Example : 

```yaml
# config/config.yaml
filter:
    google_analytics:
        hook_name: "wp_head"
        tag: "MY-GTAG-ID"
```