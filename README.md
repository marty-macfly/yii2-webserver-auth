# yii2-webserver-auth

The module allow you to restrict access to any website behind an nginx server. The authentication will be done by your Yii site and your Yii site can be use has an SSO.

You need to have Nginx Auth Request Module installed  [ngx_http_auth_request_module](http://nginx.org/en/docs/http/ngx_http_auth_request_module.html)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist "macfly/yii2-webserver-auth" "*"
```

or add

```
"macfly/yii2-webserver-auth": "*"
```

to the require section of your `composer.json` file.

Configure
------------

Configure **config/web.php** as follows

```php
  'modules' => [
     ................
    'htaccess'  => [
      'class' => 'macfly\yii\webserver\Module',
      // 'token_name' => 'mycookie', // You can change the name of the login/cookie/header in which the authentication token will be set/get
    ],
    ................
  ],
```

The module bootstrap will attached on `user` component handler `macfly\yii\webserver\events\NginxAuthEvent->setTokenCookie` on  `afterLogin` and `macfly\yii\webserver\events\NginxAuthEvent->unsetTokenCookie` on  `afterLogout`.

You need to update the configuration of your Nginx for the site you want to restrict be adding the following elements :

```
server {
    listen       80;
    server_name  www.website.com;

    location / {
        # We want to protect the root of our website
        auth_request /auth;

        root   /usr/share/nginx/html;
        index  index.html index.htm;
    }

    error_page 401 = @error401;
    location @error401 {
        # If request is made from a browser redirect to the login page of our Yii site
        if ($http_accept ~* "(text/html)|(application/xhtml+xml)") {
            # If you want the user to come back here after successful login add
            # the following query string "?return_url=http://www.website.com"
            return 302 http://yii.website.com/site/login?return_url=${scheme}://${http_host}${request_uri};
            # If you don't want the user to be redirect back just remove
            # the query string
            # return 302 http://yii.website.com/site/login;
        }

        # If request is made from a cli or a robot display a simple message
        return 401 "Unauthorized: You didn't provide the authentication token";
    }

    error_page 403 = @error403;
    location @error403 {
        # If request is made from a browser display an html page
        # saying user doesn't have enough right to access the location
        if ($http_accept ~* "(text/html)|(application/xhtml+xml)") {
            return 403;
        }

        # If request is made from a cli or a robot display a simple message
        return 403 "Forbidden: You don't have permissions to access that resource";
    }

    location = /auth {
        internal;

        # The rewrite is used to list the authorization the user required to
        #  access the location. You can also use the following :
        # /nginx/auth any logged in user can access the location
        # /nginx/auth?permission=reader any user which have 'read' permission
        # can access the location
        # /nginx/auth?permission[]=reader&permission[]=moderator any user which have 'read'
        # or 'moderator' permission can access the location

        rewrite ^(.*)$ /nginx/auth?permission[]=test&permission[]=plop? break;
        proxy_pass                      http://yii.website.com;
        proxy_pass_request_headers      on;

        # Don't forward useless data
        proxy_pass_request_body         off;
        proxy_set_header Content-Length "";
    }
}
```


Usage
------------

# From a browser

When you will go to http://www.website.com, if you don't have the cookie defined by `token_name` (default: x-sso-token) or the [`identityCookie`](http://www.yiiframework.com/doc-2.0/yii-web-user.html#$identityCookie-detail), you browser will be redirect to http://yii.website.com/site/login. After login you can go again to http://www.website.com and you will get acces to site (if your user has got the right permission).

# From a cli

You can also do authentication with cli tool, like `wget` or `curl`, in that case you will perhaps more use a header instead of a cookie. So you just need to specify the token in a header name defined by `token_name` (default: x-sso-token)


Example
------------

You can find in the `example` directory a docker-compose file to build a sample plate-form with a [yii2 basic template website](https://github.com/yiisoft/yii2-app-basic) with yii2-nginx-auth installed (in `yii/`). And an nginx container setup to authenticate on the Yii website (in `nginx/`).

You can run a test env with :

```bash
git clone https://github.com/Marty-Macfly/yii2-nginx-auth.git
cd yii2-nginx-auth/sample/yii
composer update
cd ..
docker-compose build
docker-compose up
```

- Nginx will listen on http://127.0.0.1:8888
- Yii will listen on http://127.0.0.1:8080
