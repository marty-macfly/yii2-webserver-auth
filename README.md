# yii2-webserver-auth

The module allow you to restrict access to any website behind an [Nginx](http://nginx.org/) or [Apache HTTPD](httpd.apache.org) server. With this module your Yii site is becoming your authentication and authorization provider as an LDAP or [htaccess file](httpd.apache.org/docs/current/howto/htaccess.html) can do.

Some useful usage:
- Shared login and password on multiple web-server (you don't need to update the htaccess every where to update a password), it's can be directly done on your Yii site.
- If the site behind HTTPD or Nginx is not in HTTPS, authentication is based on access Token (by default), if your token life is not too long and random it can be acceptable for a security purpose (it's always good to use ssl).
- On Nginx you've got the Single Sign-On feature, if you're already logged on your Yii website, you won't see any login page (only work if all the sites are sharing the parent domain for cookie access).
- Permission management a same account can have different access to different web-site, can access to site1 and not to site2.

## Test

There is a complete docker-compose example for Apache HTTPD and Nginx in the `example` directory, just do :

```bash
git clone https://github.com/marty-macfly/yii2-webserver-auth.git
cd yii2-webserver-auth/example/yii
composer update
cd ..
docker-compose build
docker-compose up
```

The yii site is just the [yii basic template](http://www.yiiframework.com/doc-2.0/guide-start-installation.html) with the extension installed

You can access the following components:

* Yii: http://127.0.0.1:8080
* Nginx: http://127.0.0.1:8888
* Httpd: http://127.0.0.1:8889


There is 2 users :
* admin/admin => acess token: 100-token
* demo/demo => acess token: 101-token

The login and password is used only for SSO on Nginx when you're redirect to http://127.0.0.1:8080/site/login.
If you're directly prompt by your browser for login and password you should use login: `x-sso-token` (name define in the module configuration for `token_name`) and the access token has the password (for **admin** user **100-token**).

## Yii setup

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


## Nginx setup


Installation
------------

You need to have Nginx Auth Request Module installed  [ngx_http_auth_request_module](http://nginx.org/en/docs/http/ngx_http_auth_request_module.html)

* On Debian the module is provide in `nginx-extras` so to install you just need to do :

```bash
apt-get install nginx-extras
```

Configure
------------

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

        error_page 401 = @error401;
        error_page 403 = @error403;
    }

    location @error401 {
        # If you want the user to come back here after successful login add
        # the following query string "?return_url=http://www.website.com"
        return 302 http://yii.website.com/site/login?return_url=${scheme}://${http_host}${request_uri};
        # If you don't want the user to be redirect back just remove
        # the query string
        # return 302 http://yii.website.com/site/login;
    }

    location @error403 {
        return 403;
    }

    location = /auth {
        internal;

        rewrite ^(.*)$                  /htaccess/auth? break;
        proxy_pass                      http://yii.website.com;
        proxy_pass_request_headers      on;

        # Don't forward useless data
        proxy_pass_request_body         off;
        proxy_set_header Content-Length "";
    }
}
```

You'll find in the `example/nginx/` directory a more [advanced configuration](example/nginx/conf.d/auth.conf).

Usage
------------

# From a browser

When you will go to http://www.website.com, if you don't have the cookie defined by `token_name` (default: x-sso-token) or the [`identityCookie`](http://www.yiiframework.com/doc-2.0/yii-web-user.html#$identityCookie-detail), you browser will be redirect to http://yii.website.com/site/login. After login you can go again to http://www.website.com and you will get acces to site (if your user has got the right permission).

# From a cli

You can also do authentication with cli tool, like `wget` or `curl`, in that case you can use two methods:

* Provide the access token via HTTP [Bearer Tokens](http://tools.ietf.org/html/rfc6750):

```bash
$ curl -s -i -X GET --header 'Authorization: Bearer 100-token' 'http://127.0.0.1:8888/user-sso/'
HTTP/1.1 200 OK
Server: nginx/1.10.3
Date: Tue, 28 Nov 2017 05:33:01 GMT
Content-Type: text/html
Content-Length: 359
Last-Modified: Mon, 27 Nov 2017 10:19:20 GMT
Connection: keep-alive
ETag: "5a1be6a8-167"
Accept-Ranges: bytes

<!DOCTYPE html>
<html>%   
<head>
<title>Welcome to restricted section!</title>
<style>
    body {
        width: 35em;
        margin: 0 auto;
        font-family: Tahoma, Verdana, Arial, sans-serif;
    }
</style>
</head>
<body>
<h1>Welcome to restricted section!</h1>
<p>You can see this page because you're a logged in user, sso was used.</p>
</body>
</html>
```

With an invalid token:

```bash
$ curl -s -i -X GET --header 'Authorization: Bearer bad-token' 'http://127.0.0.1:8888/user-sso/'
HTTP/1.1 401 Unauthorized
Server: nginx/1.10.3
Date: Tue, 28 Nov 2017 05:35:18 GMT
Content-Type: application/octet-stream
Content-Length: 57
Connection: keep-alive

Unauthorized: You didn't provide the authentication token
```

If you don't have permission to access the location (only in sso mode, if you're testing with no-sso return code will be 401):

```bash
$ curl -s -i -X GET --header 'Authorization: Bearer 100-token' 'http://127.0.0.1:8888/permission-sso/'
HTTP/1.1 403 Forbidden
Server: nginx/1.10.3
Date: Tue, 28 Nov 2017 05:36:01 GMT
Content-Type: application/octet-stream
Content-Length: 61
Connection: keep-alive

Forbidden: You don't have permissions to access that resource
```

* Provide the access token has a login/password, the access token is sent as the username:

```bash
$ curl -s -i -X GET 'http://100-token:@127.0.0.1:8888/user/'
HTTP/1.1 200 OK
Server: nginx/1.10.3
Date: Tue, 28 Nov 2017 05:39:52 GMT
Content-Type: text/html
Content-Length: 359
Last-Modified: Mon, 27 Nov 2017 10:19:20 GMT
Connection: keep-alive
ETag: "5a1be6a8-167"
Accept-Ranges: bytes

<!DOCTYPE html>
<html>
<head>
<title>Welcome to restricted section!</title>
<style>
    body {
        width: 35em;
        margin: 0 auto;
        font-family: Tahoma, Verdana, Arial, sans-serif;
    }
</style>
</head>
<body>
<h1>Welcome to restricted section!</h1>
<p>You can see this page because you're a logged in user, sso was used.</p>
</body>
</html>
```

## Apache Httpd setup

Installation
------------

You need to install and enable the module [mod_authnz_external](https://github.com/kitech/mod_authnz_external/blob/master/INSTALL).

* On Debian the module is provide in `libapache2-mod-authnz-external` so to install you just need to do :

```bash
apt-get install libapache2-mod-authnz-external
```

Because we're using a shell script to do the request you also need to install `curl`.

```bash
apt-get install curl
```

You need to put the script [`apache-auth.sh`](example/httpd/apache-auth.sh), on your server which will launch the proper curl command :

Configure
------------

You need to update the configuration of your Httpd server for the site you want to restrict be adding the following elements :

```
<VirtualHost *:80>
	ServerName www.website.com
	DocumentRoot /var/www/html

	DefineExternalAuth auth pipe '/apache-auth.sh http://yii.website.com/htaccess/auth'

	<Directory "/var/www/html/user">
		Options Indexes FollowSymLinks

		AuthType Basic
		AuthName "Authentication Required"
		Require valid-user
		AuthBasicProvider external
		AuthExternal auth
		AllowOverride None
	</Directory>
</VirtualHost>
```

You'll find in the `example/httpd/` directory a more [advanced configuration](example/httpd/default.conf).

# From a cli

You can also do authentication with cli tool, like `wget` or `curl`, in that case you can use following method:

* Provide the access token has a login/password, the access token is sent as the username:

```bash
$ curl -s -i -X GET 'http://100-token:@127.0.0.1:8889/user/'
HTTP/1.1 200 OK
Date: Tue, 28 Nov 2017 05:44:46 GMT
Server: Apache/2.4.25 (Debian)
Last-Modified: Mon, 27 Nov 2017 10:58:40 GMT
ETag: "159-55ef4c8f3b800"
Accept-Ranges: bytes
Content-Length: 345
Vary: Accept-Encoding
Content-Type: text/html

<!DOCTYPE html>
<html>
<head>
<title>Welcome to restricted section!</title>
<style>
    body {
        width: 35em;
        margin: 0 auto;
        font-family: Tahoma, Verdana, Arial, sans-serif;
    }
</style>
</head>
<body>
<h1>Welcome to restricted section!</h1>
<p>You can see this page because you're a logged in user.</p>
</body>
</html>
```

With an invalid token:

```bash
$ curl -s -i -X GET 'http://bad-token:127.0.0.1:8889/user/'
HTTP/1.1 401 Unauthorized
Date: Tue, 28 Nov 2017 05:45:27 GMT
Server: Apache/2.4.25 (Debian)
WWW-Authenticate: Basic realm="Authentication Required"
Content-Length: 458
Content-Type: text/html; charset=iso-8859-1

<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>401 Unauthorized</title>
</head><body>
<h1>Unauthorized</h1>
<p>This server could not verify that you
are authorized to access the document
requested.  Either you supplied the wrong
credentials (e.g., bad password), or your
browser doesn't understand how to supply
the credentials required.</p>
<hr>
<address>Apache/2.4.25 (Debian) Server at 127.0.0.1 Port 8889</address>
</body></html>
```
