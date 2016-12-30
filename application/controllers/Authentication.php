<?php
require_once APPPATH . "libraries/Facebook/autoload.php";
defined('BASEPATH') OR exit('No direct script access allowed');

//use Facebook;
class Authentication extends CI_Controller {

    function __construct() {
        parent::__construct();
        // Load user model
        $this->load->helper('url');
        $this->load->helper('email');
        $this->load->helper('cookie');
        $this->load->model('Common_model');
        $this->load->language('common');
        $this->load->library('session');
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index() {     
        // Include the google api php libraries
        include_once APPPATH . "libraries/google-api-php-client/Google_Client.php";
        include_once APPPATH . "libraries/google-api-php-client/contrib/Google_Oauth2Service.php";

        // Google Client Configuration
        $gClient = new Google_Client();
        $gClient->setApplicationName('Google Login');
        $gClient->setClientId(Google_clientId);
        $gClient->setClientSecret(Google_clientSecret);
        $gClient->setRedirectUri(Google_redirectUrl);
        $google_oauthV2 = new Google_Oauth2Service($gClient);

        if (isset($_REQUEST['code'])) {
            $gClient->authenticate();
            $this->session->set_userdata('token', $gClient->getAccessToken());
            redirect(Google_redirectUrl);
        }

        $token = $this->session->userdata('token');
        if (!empty($token)) {
            $gClient->setAccessToken($token);
        }

        if ($gClient->getAccessToken()) {
            $userProfile = $google_oauthV2->userinfo->get();
            //echo "<pre>";print_r($userProfile);die;
            $userData['oauth_provider'] = 'google';
            $userData['oauth_uid'] = $userProfile['id'];
            $userData['first_name'] = $userProfile['given_name'];
            $userData['last_name'] = $userProfile['family_name'];
            $userData['email'] = $userProfile['email'];
            $userData['gender'] = $userProfile['gender'];
            $userData['locale'] = $userProfile['locale'];
            $userData['profile_url'] = $userProfile['link'];
            $userData['picture_url'] = $userProfile['picture'];

            $data['userData'] = $userData;
        } else {
            $data['authUrl'] = $gClient->createAuthUrl();
        }

        $fb = new Facebook\Facebook([
            'app_id' => Facebook_appId, // Replace {app-id} with your app id
            'app_secret' => Facebook_secret,
            'default_graph_version' => 'v2.8',
        ]);

        $redirectUrl = Facebook_redirectUrl;
        $permissions = ['email']; // Optional permissions
        
        $helper = $fb->getRedirectLoginHelper();

        $loginUrl = $helper->getLoginUrl($redirectUrl, $permissions);

        $data['login_url'] = $loginUrl;

        $this->load->view('authentication/index', $data);
    }

    public function callback() {
        include_once APPPATH . "libraries/google-api-php-client/Google_Client.php";
        include_once APPPATH . "libraries/google-api-php-client/contrib/Google_Oauth2Service.php";

        // Google Client Configuration
        $gClient = new Google_Client();
        $gClient->setApplicationName('Google Login');
        $gClient->setClientId(Google_clientId);
        $gClient->setClientSecret(Google_clientSecret);
        $gClient->setRedirectUri(Google_redirectUrl);
        $google_oauthV2 = new Google_Oauth2Service($gClient);

        if (isset($_REQUEST['code'])) {
            $gClient->authenticate();
            $this->session->set_userdata('token', $gClient->getAccessToken());
            redirect(Google_redirectUrl);
        }

        $token = $this->session->userdata('token');
        if (!empty($token)) {
            $gClient->setAccessToken($token);
        }

        if ($gClient->getAccessToken()) {
            $userProfile = $google_oauthV2->userinfo->get();
            // Preparing data for database insertion
            $userData['oauth_provider'] = 'Google';
            $userData['oauth_uid'] = $userProfile['id'];
            $userData['first_name'] = $userProfile['given_name'];
            $userData['last_name'] = $userProfile['family_name'];
            $userData['email'] = $userProfile['email'];
            $userData['gender'] = $userProfile['gender'];
            $userData['locale'] = $userProfile['locale'];
            $userData['profile_url'] = $userProfile['link'];
            $userData['picture_url'] = $userProfile['picture'];

            $data['userData'] = $userData;
        } else {
            $data['authUrl'] = $gClient->createAuthUrl();
        }
        $this->load->view('authentication/index', $data);
    }

    public function callbackfb() {
        
        $fb = new Facebook\Facebook([
            'app_id' => Facebook_appId, // Replace {app-id} with your app id
            'app_secret' => Facebook_secret,
            'default_graph_version' => 'v2.8',
        ]);
        $redirectUrl = Facebook_redirectUrl;
        
        $helper = $fb->getRedirectLoginHelper();

        if (isset($_REQUEST['code'])) {
            //$gClient->authenticate();
            $this->session->set_userdata('token', $helper->getAccessToken());
            redirect($redirectUrl);
        }
      
        try {
            //$accessToken = $helper->getAccessToken();
            $accessToken = $this->session->userdata('token');
            $response = $fb->get('/me?fields=id,name,gender,email,birthday', $accessToken);
            $pic= $fb->get('/me/picture?type=large', $accessToken);
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (!isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }

        // Logged in
        //echo '<h3>Access Token</h3>';
        //var_dump($accessToken->getValue());

        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        //echo '<h3>Metadata</h3>';
        //var_dump($tokenMetadata);

        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId(Facebook_appId); // Replace {app-id} with your app id
        // If you know the user ID this access token belongs to, you can validate it here
        //$tokenMetadata->validateUserId('123');
        $tokenMetadata->validateExpiration();

        if (!$accessToken->isLongLived()) {
            // Exchanges a short-lived access token for a long-lived one
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
                exit;
            }

            echo '<h3>Long-lived</h3>';
            var_dump($accessToken->getValue());
        }

       // $_SESSION['fb_access_token'] = (string) $accessToken;


        if ($response) {
            //echo "<img src='".$pic->getHeaders()['Location']."'>";
            $userProfile=$response->getDecodedBody();
            //echo "<pre>";print_r($response->getDecodedBody());die;
            // Preparing data for database insertion
            $userData['oauth_provider'] = 'Facebbok';
            $userData['oauth_uid'] = $userProfile['id'];
            $userData['first_name'] = $userProfile['name'];
            $userData['last_name'] = '';
            $userData['email'] = $userProfile['email'];
            $userData['gender'] = $userProfile['gender'];
            //$userData['locale'] = $userProfile['locale'];
            $userData['profile_url'] = '';
            $userData['picture_url'] = $pic->getHeaders()['Location'];

            $data['userData'] = $userData;
        } 
        
        $this->load->view('authentication/index', $data);
        // User is logged in with a long-lived access token.
        // You can redirect them to a members-only page.
        //header('Location: https://example.com/members.php');
    }

    public function logout() {
        //echo "<pre>";print_r($this->session);die;
        $this->session->unset_userdata('token');
        $this->session->unset_userdata('userData');
        $this->session->sess_destroy();
        redirect('/authentication');
    }

}
