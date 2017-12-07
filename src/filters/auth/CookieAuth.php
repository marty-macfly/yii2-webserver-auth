<?php
namespace  macfly\yii\webserver\auth;
/**
 * CookieAuth is an action filter that supports the cookie authentication method.
 *
 * You may use CookieAuth by attaching it as a behavior to a controller or module, like the following:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'cookieAuth' => [
 *             'class' => \macfly\yii\webserver\auth\CookieAuth::className(),
 *         ],
 *     ];
 * }
 * ```
 *
 * The default implementation of CookieAuth uses the [[\yii\web\User::loginByAccessToken()|loginByAccessToken()]]
 * method of the `user` application component.
 *
 * @author Charles Delfly <charles@delfly.fr>
 */
class CookieAuth extends AuthMethod
{
    /**
     * @var string the Cookie name
     */
    public $cookieName = 'x-sso-token';

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $accessToken = $request->cookies->getValue($this->cookieName);

        if (is_string($accessToken)) {
            $identity = $user->loginByAccessToken($accessToken, get_class($this));
            if ($identity !== null) {
                return $identity;
            }
        }

        if ($accessToken !== null) {
            $this->handleFailure($response);
        }

        return null;
    }
}
