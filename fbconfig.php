<?php
    if(!session_id()) { session_start(); }

    require_once 'includes/imp_files.php';
    require_once 'vendor/autoload.php';

    $fb = new Facebook\Facebook([
        'app_id' => APP_ID,
        'app_secret' => APP_SECRET,
        'default_graph_version' => GRAPH_VERSION
    ]);

    $helper = $fb->getRedirectLoginHelper();

    $accessToken = $helper->getAccessToken();

    if (isset($accessToken) || isset($_SESSION['fb_token'])) {
        $_SESSION['fb_token'] = isset($accessToken) ? (string) $accessToken : $_SESSION['fb_token'];

        // redirect the user back to the same page if it has "code" GET variable
        if (isset($_GET['code'])) {
            //header('Location: ./?error=err');
        }

        // checking if user access token is not valid then ask user to login again
        $debugToken = $fb->get('/debug_token?input_token='. $_SESSION['fb_token'], APP_ID . '|' . APP_SECRET)
            ->getGraphNode()
            ->asArray();

        if (isset($debugToken['error']['code'])) {
            unset($_SESSION['fb_token']);
            $loginUrl = $helper->getLoginUrl(APP_URL);
            echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
            exit;
        }

        // setting default user access token for future requests
        $fb->setDefaultAccessToken($_SESSION['fb_token']);

        try {
            // Get the \Facebook\GraphNodes\GraphUser object for the current user.
            // If you provided a 'default_access_token', the '{access-token}' is optional.
            $response = $fb->get('/me?fields=name,first_name,last_name,email,picture', $_SESSION['fb_token']);
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $user = $response->getGraphUser();
        echo '<pre>'.print_r($user).'</pre>';
    } else {
        // making login with facebook url
        $loginUrl = $helper->getLoginUrl(APP_URL);
        echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
    }