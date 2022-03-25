<?php

use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;






class UserController extends Controller
{

    public function indexAction()
    {

        $response = new Response();

        $session = $this->session;

        $login = $session->get('login');
        $log = $this->cookies->get('login');
        $checklog = $log->getValue();

        $check = $this->request->get('log');

        if (($checklog || $login) && $check != 'logout') {

            /**
             * fetching date time from datetime 
             */
            $time = $this->datetime;
            $this->view->time = $time;
            $user = new Users();
            $this->view->users = $user->getUsers();
        } else {
            $session->destroy();
            setcookie('login', 0, time() + (86400 * 30), "/");
            return $response->redirect('user/login');
        }
    }

    /**
     * signupAction()
     * controller function to handle signup view
     *
     * @return void
     */
    public function signupAction()
    {
        $escaper = new \App\Controller\Myescaper();
        $logger = new \App\Controller\MyLogger();
        $response = new Response();


        $userArr = [
            'name' => $escaper->sanitize($this->request->getPost()['name']),
            'email' => $escaper->sanitize($this->request->getPost()['email']),
            'password' => $escaper->sanitize($this->request->getPost()['password']),
        ];
        $input = $escaper->sanitize($userArr);
        $user = new Users();

        $user->assign(
            $input,
            [
                'name',
                'email',
                'password',
            ]
        );
        $checkMail = $user->checkMail($escaper->sanitize($this->request->getPost()['email']));
        if (!$checkMail) {
            $success = $user->save();
            if ($success) {
                unset($_POST);
                $_POST = array();
                return $response->redirect('/user');
            } else {

                $message = implode($user->getMessages());
                $this->view->message = $message;
                $logger->log('signup', $message);
            }
        } else {
            $logger->log('signup', 'email already exists');
            $message = 'email already exists';
            $this->view->message = $message;
        }
    }

    /**
     * loginAction
     * controller to handle login view
     *
     * @return void
     */
    public function loginAction()
    {

        $logger = new \App\Controller\MyLogger();
        $session = $this->session;
        $escaper = new \App\Controller\Myescaper();
        $response = new Response();
        $this->view->message = "";
        /**
         * checking for post request
         */
        $check = $this->request->isPost();
        if ($check) {

            if ($this->request->getPost()['email'] && $this->request->getPost()['password']) {
                $email = $escaper->sanitize($this->request->getPost()['email']);
                $password = $escaper->sanitize($this->request->getPost()['password']);
                $user = new Users();
                $data = $user->checkUser($email, $password);
                if ($data) {

                    /**
                     * if remember is checked setting cookie
                     */
                    $remember = $this->request->getPost()['remember'];
                    if ($remember == 'on') {

                        $this->cookies->set('login', 1, time() + (86400 * 30), "/");
                        $this->cookies->send();
                    }

                    $session->set('login', 1);
                    $session->login = 1;
                    return $response->redirect('/user');
                } else {

                    $this->view->message = 'authentication failed';

                    unset($_POST);
                    $_POST = array();
                    $logger->log('login', 'invalid credentials');
                    /**
                     * sending response 403 if authentication fails
                     */
                    $response->setStatusCode(403, 'Authentication Failed');
                    $response->setContent("Authenication failed");
                    $response->send();
                }
            } else {
                $this->view->message = 'please fill all fields';
                if (!$this->request->getPost()['email']) {
                    $logger->log('login', 'email is required');
                }

                if (!$this->request->getPost()['password']) {

                    $logger->log('login', 'password is required');
                }
            }
        }
    }
}
