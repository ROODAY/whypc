<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Why PC?</title>
        <meta name="description" content="">
        <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1">
        <meta name="msapplication-tap-highlight" content="no" />
        <meta name="msapplication-TileColor" content="#FFFFFF">
        <meta name="msapplication-TileImage" content="assets/favicons/favicon-144.png">
        <meta name="msapplication-config" content="assets/favicons/browserconfig.xml">

        <link rel="shortcut icon" href="assets/favicons/favicon.ico">
        <link rel="icon" sizes="16x16 32x32 64x64" href="assets/favicons/favicon.ico">
        <link rel="icon" type="image/png" sizes="196x196" href="assets/favicons/favicon-196.png">
        <link rel="icon" type="image/png" sizes="160x160" href="assets/favicons/favicon-160.png">
        <link rel="icon" type="image/png" sizes="96x96" href="assets/favicons/favicon-96.png">
        <link rel="icon" type="image/png" sizes="64x64" href="assets/favicons/favicon-64.png">
        <link rel="icon" type="image/png" sizes="32x32" href="assets/favicons/favicon-32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="assets/favicons/favicon-16.png">
        <link rel="apple-touch-icon" sizes="152x152" href="assets/favicons/favicon-152.png">
        <link rel="apple-touch-icon" sizes="144x144" href="assets/favicons/favicon-144.png">
        <link rel="apple-touch-icon" sizes="120x120" href="assets/favicons/favicon-120.png">
        <link rel="apple-touch-icon" sizes="114x114" href="assets/favicons/favicon-114.png">
        <link rel="apple-touch-icon" sizes="76x76" href="assets/favicons/favicon-76.png">
        <link rel="apple-touch-icon" sizes="72x72" href="assets/favicons/favicon-72.png">
        <link rel="apple-touch-icon" href="assets/favicons/favicon-57.png">
        
        <link href='http://fonts.googleapis.com/css?family=Noto+Sans' rel='stylesheet' type='text/css'>
        <link href='http://fonts.googleapis.com/css?family=Comfortaa' rel='stylesheet' type='text/css'>
        
        <link rel="stylesheet" href="css/vendor/bootstrap.min.css">
        <style>
            #loading {
                width: 100vw;
                height: 100vh;
                background-color: #FFF8AF;
                font-size: 2em;
                font-family: 'Comfortaa', serif;
                position: fixed;
                z-index: 1000000;
            }
            #loading * {
                display: block;
            }
            #loading span {
                margin-top: 40vh;
                margin-left: 42vw;
            }
            #loading img {
                margin-left: 43vw;
            }
        </style>
        <link rel="stylesheet" href="css/vendor/animate.css">
        <link rel="stylesheet" href="css/vendor/jquery.mCustomScrollbar.css" />
        <link rel="stylesheet" href="css/main.css">

        <script src="js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
    </head>
    <body>
        <?php
        /**
         * Class OneFileLoginApplication
         *
         * An entire php application with user registration, login and logout in one file.
         * Uses very modern password hashing via the PHP 5.5 password hashing functions.
         * This project includes a compatibility file to make these functions available in PHP 5.3.7+ and PHP 5.4+.
         *
         * @author Panique
         * @link https://github.com/panique/php-login-one-file/
         * @license http://opensource.org/licenses/MIT MIT License
         */
        class OneFileLoginApplication
        {
            /**
             * @var string Type of used database (currently only SQLite, but feel free to expand this with mysql etc)
             */
            private $db_type = "sqlite"; //

            /**
             * @var string Path of the database file (create this with _install.php)
             */
            private $db_sqlite_path = "/users.db";

            /**
             * @var object Database connection
             */
            private $db_connection = null;

            /**
             * @var bool Login status of user
             */
            private $user_is_logged_in = false;

            /**
             * @var string System messages, likes errors, notices, etc.
             */
            public $feedback = "";


            /**
             * Does necessary checks for PHP version and PHP password compatibility library and runs the application
             */
            public function __construct()
            {
                if ($this->performMinimumRequirementsCheck()) {
                    $this->runApplication();
                }
            }

            /**
             * Performs a check for minimum requirements to run this application.
             * Does not run the further application when PHP version is lower than 5.3.7
             * Does include the PHP password compatibility library when PHP version lower than 5.5.0
             * (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
             * @return bool Success status of minimum requirements check, default is false
             */
            private function performMinimumRequirementsCheck()
            {
                if (version_compare(PHP_VERSION, '5.3.7', '<')) {
                    echo "Sorry, Simple PHP Login does not run on a PHP version older than 5.3.7 !";
                } elseif (version_compare(PHP_VERSION, '5.5.0', '<')) {
                    require_once("libraries/password_compatibility_library.php");
                    return true;
                } elseif (version_compare(PHP_VERSION, '5.5.0', '>=')) {
                    return true;
                }
                // default return
                return false;
            }

            /**
             * This is basically the controller that handles the entire flow of the application.
             */
            public function runApplication()
            {
                // check is user wants to see register page (etc.)
                if (isset($_GET["action"]) && $_GET["action"] == "register") {
                    $this->doRegistration();
                    $this->showPageRegistration();
                } else {
                    // start the session, always needed!
                    $this->doStartSession();
                    // check for possible user interactions (login with session/post data or logout)
                    $this->performUserLoginAction();
                    // show "page", according to user's login status
                    if ($this->getUserLoginStatus()) {
                        $this->showPageLoggedIn();
                    } else {
                        $this->showPageLoginForm();
                    }
                }
            }

            /**
             * Creates a PDO database connection (in this case to a SQLite flat-file database)
             * @return bool Database creation success status, false by default
             */
            private function createDatabaseConnection()
            {
                try {
                    $this->db_connection = new PDO($this->db_type . ':' . $this->db_sqlite_path);
                    return true;
                } catch (PDOException $e) {
                    $this->feedback = "PDO database connection problem: " . $e->getMessage();
                } catch (Exception $e) {
                    $this->feedback = "General problem: " . $e->getMessage();
                }
                return false;
            }

            /**
             * Handles the flow of the login/logout process. According to the circumstances, a logout, a login with session
             * data or a login with post data will be performed
             */
            private function performUserLoginAction()
            {
                if (isset($_GET["action"]) && $_GET["action"] == "logout") {
                    $this->doLogout();
                } elseif (!empty($_SESSION['user_name']) && ($_SESSION['user_is_logged_in'])) {
                    $this->doLoginWithSessionData();
                } elseif (isset($_POST["login"])) {
                    $this->doLoginWithPostData();
                }
            }

            /**
             * Simply starts the session.
             * It's cleaner to put this into a method than writing it directly into runApplication()
             */
            private function doStartSession()
            {
                session_start();
            }

            /**
             * Set a marker (NOTE: is this method necessary ?)
             */
            private function doLoginWithSessionData()
            {
                $this->user_is_logged_in = true; // ?
            }

            /**
             * Process flow of login with POST data
             */
            private function doLoginWithPostData()
            {
                if ($this->checkLoginFormDataNotEmpty()) {
                    if ($this->createDatabaseConnection()) {
                        $this->checkPasswordCorrectnessAndLogin();
                    }
                }
            }

            /**
             * Logs the user out
             */
            private function doLogout()
            {
                $_SESSION = array();
                session_destroy();
                $this->user_is_logged_in = false;
                $this->feedback = "You were just logged out.";
            }

            /**
             * The registration flow
             * @return bool
             */
            private function doRegistration()
            {
                if ($this->checkRegistrationData()) {
                    if ($this->createDatabaseConnection()) {
                        $this->createNewUser();
                    }
                }
                // default return
                return false;
            }

            /**
             * Validates the login form data, checks if username and password are provided
             * @return bool Login form data check success state
             */
            private function checkLoginFormDataNotEmpty()
            {
                if (!empty($_POST['user_name']) && !empty($_POST['user_password'])) {
                    return true;
                } elseif (empty($_POST['user_name'])) {
                    $this->feedback = "Username field was empty.";
                } elseif (empty($_POST['user_password'])) {
                    $this->feedback = "Password field was empty.";
                }
                // default return
                return false;
            }

            /**
             * Checks if user exits, if so: check if provided password matches the one in the database
             * @return bool User login success status
             */
            private function checkPasswordCorrectnessAndLogin()
            {
                // remember: the user can log in with username or email address
                $sql = 'SELECT user_name, user_email, user_password_hash
                        FROM users
                        WHERE user_name = :user_name OR user_email = :user_name
                        LIMIT 1';
                $query = $this->db_connection->prepare($sql);
                $query->bindValue(':user_name', $_POST['user_name']);
                $query->execute();

                // Btw that's the weird way to get num_rows in PDO with SQLite:
                // if (count($query->fetchAll(PDO::FETCH_NUM)) == 1) {
                // Holy! But that's how it is. $result->numRows() works with SQLite pure, but not with SQLite PDO.
                // This is so crappy, but that's how PDO works.
                // As there is no numRows() in SQLite/PDO (!!) we have to do it this way:
                // If you meet the inventor of PDO, punch him. Seriously.
                $result_row = $query->fetchObject();
                if ($result_row) {
                    // using PHP 5.5's password_verify() function to check password
                    if (password_verify($_POST['user_password'], $result_row->user_password_hash)) {
                        // write user data into PHP SESSION [a file on your server]
                        $_SESSION['user_name'] = $result_row->user_name;
                        $_SESSION['user_email'] = $result_row->user_email;
                        $_SESSION['user_is_logged_in'] = true;
                        $this->user_is_logged_in = true;
                        return true;
                    } else {
                        $this->feedback = "Wrong password.";
                    }
                } else {
                    $this->feedback = "This user does not exist.";
                }
                // default return
                return false;
            }

            /**
             * Validates the user's registration input
             * @return bool Success status of user's registration data validation
             */
            private function checkRegistrationData()
            {
                // if no registration form submitted: exit the method
                if (!isset($_POST["register"])) {
                    return false;
                }

                // validating the input
                if (!empty($_POST['user_name'])
                    && strlen($_POST['user_name']) <= 64
                    && strlen($_POST['user_name']) >= 2
                    && preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])
                    && !empty($_POST['user_email'])
                    && strlen($_POST['user_email']) <= 64
                    && filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)
                    && !empty($_POST['user_password_new'])
                    && !empty($_POST['user_password_repeat'])
                    && ($_POST['user_password_new'] === $_POST['user_password_repeat'])
                ) {
                    // only this case return true, only this case is valid
                    return true;
                } elseif (empty($_POST['user_name'])) {
                    $this->feedback = "Empty Username";
                } elseif (empty($_POST['user_password_new']) || empty($_POST['user_password_repeat'])) {
                    $this->feedback = "Empty Password";
                } elseif ($_POST['user_password_new'] !== $_POST['user_password_repeat']) {
                    $this->feedback = "Password and password repeat are not the same";
                } elseif (strlen($_POST['user_password_new']) < 6) {
                    $this->feedback = "Password has a minimum length of 6 characters";
                } elseif (strlen($_POST['user_name']) > 64 || strlen($_POST['user_name']) < 2) {
                    $this->feedback = "Username cannot be shorter than 2 or longer than 64 characters";
                } elseif (!preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])) {
                    $this->feedback = "Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters";
                } elseif (empty($_POST['user_email'])) {
                    $this->feedback = "Email cannot be empty";
                } elseif (strlen($_POST['user_email']) > 64) {
                    $this->feedback = "Email cannot be longer than 64 characters";
                } elseif (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
                    $this->feedback = "Your email address is not in a valid email format";
                } else {
                    $this->feedback = "An unknown error occurred.";
                }

                // default return
                return false;
            }

            /**
             * Creates a new user.
             * @return bool Success status of user registration
             */
            private function createNewUser()
            {
                // remove html code etc. from username and email
                $user_name = htmlentities($_POST['user_name'], ENT_QUOTES);
                $user_email = htmlentities($_POST['user_email'], ENT_QUOTES);
                $user_password = $_POST['user_password_new'];
                // crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 char hash string.
                // the constant PASSWORD_DEFAULT comes from PHP 5.5 or the password_compatibility_library
                $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);

                $sql = 'SELECT * FROM users WHERE user_name = :user_name OR user_email = :user_email';
                $query = $this->db_connection->prepare($sql);
                $query->bindValue(':user_name', $user_name);
                $query->bindValue(':user_email', $user_email);
                $query->execute();

                // As there is no numRows() in SQLite/PDO (!!) we have to do it this way:
                // If you meet the inventor of PDO, punch him. Seriously.
                $result_row = $query->fetchObject();
                if ($result_row) {
                    $this->feedback = "Sorry, that username / email is already taken. Please choose another one.";
                } else {
                    $sql = 'INSERT INTO users (user_name, user_password_hash, user_email)
                            VALUES(:user_name, :user_password_hash, :user_email)';
                    $query = $this->db_connection->prepare($sql);
                    $query->bindValue(':user_name', $user_name);
                    $query->bindValue(':user_password_hash', $user_password_hash);
                    $query->bindValue(':user_email', $user_email);
                    // PDO's execute() gives back TRUE when successful, FALSE when not
                    // @link http://stackoverflow.com/q/1661863/1114320
                    $registration_success_state = $query->execute();

                    if ($registration_success_state) {
                        $this->feedback = "Your account has been created successfully. You can now log in.";
                        return true;
                    } else {
                        $this->feedback = "Sorry, your registration failed. Please go back and try again.";
                    }
                }
                // default return
                return false;
            }

            /**
             * Simply returns the current status of the user's login
             * @return bool User's login status
             */
            public function getUserLoginStatus()
            {
                return $this->user_is_logged_in;
            }

            /**
             * Simple demo-"page" that will be shown when the user is logged in.
             * In a real application you would probably include an html-template here, but for this extremely simple
             * demo the "echo" statements are totally okay.
             */
            private function showPageLoggedIn()
            {
                if ($this->feedback) {
                    echo $this->feedback . "<br/><br/>";
                }

                echo 'Hello ' . $_SESSION['user_name'] . ', you are logged in.<br/><br/>';
                echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?action=logout">Log out</a>';
            }

            /**
             * Simple demo-"page" with the login form.
             * In a real application you would probably include an html-template here, but for this extremely simple
             * demo the "echo" statements are totally okay.
             */
            private function showPageLoginForm()
            {
                if ($this->feedback) {
                    echo $this->feedback . "<br/><br/>";
                }

                echo '<h2>Login</h2>';

                echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '" name="loginform">';
                echo '<label for="login_input_username">Username (or email)</label> ';
                echo '<input id="login_input_username" type="text" name="user_name" required /> ';
                echo '<label for="login_input_password">Password</label> ';
                echo '<input id="login_input_password" type="password" name="user_password" required /> ';
                echo '<input type="submit"  name="login" value="Log in" />';
                echo '</form>';

                echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?action=register">Register new account</a>';
            }

            /**
             * Simple demo-"page" with the registration form.
             * In a real application you would probably include an html-template here, but for this extremely simple
             * demo the "echo" statements are totally okay.
             */
            private function showPageRegistration()
            {
                if ($this->feedback) {
                    echo $this->feedback . "<br/><br/>";
                }

                echo '<h2>Registration</h2>';

                echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?action=register" name="registerform">';
                echo '<label for="login_input_username">Username (only letters and numbers, 2 to 64 characters)</label>';
                echo '<input id="login_input_username" type="text" pattern="[a-zA-Z0-9]{2,64}" name="user_name" required />';
                echo '<label for="login_input_email">User\'s email</label>';
                echo '<input id="login_input_email" type="email" name="user_email" required />';
                echo '<label for="login_input_password_new">Password (min. 6 characters)</label>';
                echo '<input id="login_input_password_new" class="login_input" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" />';
                echo '<label for="login_input_password_repeat">Repeat password</label>';
                echo '<input id="login_input_password_repeat" class="login_input" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" />';
                echo '<input type="submit" name="register" value="Register" />';
                echo '</form>';

                echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '">Homepage</a>';
            }
        }

        // run the application
        $application = new OneFileLoginApplication();

        ?>
        <!--[if lt IE 7]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
        <div id="loading">
            <span>Loading...</span>
            <img src="assets/images/loading.svg" alt="Loading icon" />
        </div>
        <div style="margin-top: -100vh; position: fixed; z-index: 100000" id="easter"><img src="assets/favicons/favicon-310.png"></div>
        <a href="builds.html"><span class="glyphicon glyphicon-th-list tooltipper" style="font-size: 1.5em; color: #333; margin-left: 0.25em; margin-top: 0.25em; position: fixed; z-index: 100000; opacity: 0.25;" id="switchpage" data-toggle="tooltip" data-placement="right" title="PC Builds"></span></a>
        <div class="menu navtool hidden-xs hidden-sm" id="navmenu">
            <span class="menu-global menu-top"></span>
            <span class="menu-global menu-middle"></span>
            <span class="menu-global menu-bottom"></span>
        </div>
        <div class="navtool2" id="navupdown">
            <span class="glyphicon glyphicon-circle-arrow-up navarrow" style="font-size: 2.3em; color: #333;"id="navup" title="Previous Section"></span>
            <span class="glyphicon glyphicon-circle-arrow-down navarrow" style="font-size: 2.3em; color: #333;"id="navdown" title="Next Section"></span>
            <span class="glyphicon glyphicon-refresh navarrow hidden-xs hidden-sm" style="font-size: 2.3em; color: #333;"id="navfix" title="Current Section"></span>
        </div>
        <nav class="navtool hidden-xs hidden-sm" id="links">
            <ul class="nav" style="width: 12em;" id="listentries">
                <li><a href="#cover">Top</a></li>
                <li><a href="#goodconsoles">Good Consoles</a></li>
                <li><a href="#badconsoles">Bad Consoles</a></li>
                <li><a href="#sect1">Summary</a></li>
                <li><a href="#sect2">PC vs. Console Price</a></li>
                <li><a href="#sect3">Performace and Quality Ceiling</a></li>
                <li><a href="#sect4">Building vs. Buying</a></li>
                <li><a href="#sect5">Building isn't Hard</a></li>
                <li><a href="#sect6">OSX/Linux</a></li>
                <li><a href="#sect7">Price of PC Games</a></li>
                <li><a href="#sect8">Virtual Reality</a></li>
                <li><a href="#sect9">ESPorts</a></li>
                <li><a href="#sect10">Compatibility</a></li>
                <li><a href="#sect11">Social Capability</a></li>
                <li><a href="#sect12">Family Friendliness</a></li>
                <li><a href="#sect13">Media Consumption</a></li>
                <li><a href="#sect14">Socially Capable</a></li>
                <li><a href="#sect15">Local Multiplayer</a></li>
                <li><a href="#sect16">Online Features</a></li>
                <li><a href="#sect17">Online Play</a></li>
                <li><a href="#sect18">Modding</a></li>
                <li><a href="#sect19">Shapes and Sizes</a></li>
                <li><a href="#sect20">Tax Write-Offs</a></li>
                <li><a href="#sect21">30 vs 60 FPS</a></li>
                <li><a href="#sect22">Sub 46 FPS Eye Strain</a></li>
                <li><a href="#sect23">60 vs 90 FOV</a></li>
                <li><a href="#sect24">Academic Studies on FPS</a></li>
                <li><a href="#sect25">Optimization</a></li>
                <li><a href="#sect26">Overclocking</a></li>
                <li><a href="#sect27">Sharing Games</a></li>
                <li><a href="#sect28">Games are always yours</a></li>
                <li><a href="#sect29">Couch Gaming</a></li>
                <li><a href="#sect30">Needs revising</a></li>
                <li><a href="#sect31">Consoles are still behind PCs</a></li>
                <li><a href="#sect32">Needs revision</a></li>
                <li><a href="#sect33">Cleaning/Upgrading/Repairs</a></li>
                <li><a href="#sect34">Warranties</a></li>
                <li><a href="#sect35">Voice Communication</a></li>
                <li><a href="#sect36">Platform Exclusives</a></li>
                <li><a href="#sect37">Input Devices</a></li>
                <li><a href="#sect38">Legacy Support</a></li>
                <li><a href="#sect39">Terminalization</a></li>
                <li><a href="#sect40">Capture Cards</a></li>
                <li><a href="#sect41">Spare Processing</a></li>
                <li><a href="#sect42">PC Gaming</a></li>
                <li><a href="#sect43">Consoles don't Innovate</a></li>
                <li><a href="#sect44">Companies make more money from PC</a></li>
                <li><a href="#sect45">Used Game Sales are bad</a></li>
                <li><a href="#sect46">Piracy</a></li>
                <li><a href="#sect47">"Next-Gen" Gameplay/Screenshots</a></li>
                <li><a href="#sect48">Console Gamers are not the Enemy</a></li>
                <li><a href="#sect49">Further Reading</a></li>
                <li><a href="#sect50">Author's Note</a></li>
                <li><a href="#sect51">Editor's Note</a></li>
                <li><a href="#sect52">Sharing this Guide</a></li>
                <li><a href="#sect53">Reader Response</a></li>
                <li><a href="#sect54">Translations</a></li>
                <li><a href="#sect55">Statistics</a></li>
                <li><a href="#sect56">Suggestions</a></li>
                <li><a href="#sect57">Poll</a></li>
            </ul>
        </nav>
        <div class="container-fluid" id="cover">
            <div style="text-align: center;">
                <h1><span style="font-size: 6em;" id="coverheader">Why PC&quest;</span><br><br>
                <small>The no-nonsense&comma; end all guide to why console gaming is no longer worth your time</small></h1><br>
                <a href="#goodconsoles"><span class="glyphicon glyphicon-circle-arrow-down animated pulse" style="font-size: 4em; color: #E8AE1F;"></span></a>
            </div>
        </div>
        <main data-spy="scroll" data-target="#listentries">
            <div class="container-fluid" id="goodconsoles">
                <span class="text-center">
                    <h1>The Golden Age of Consoles<br>
                    <small>Back when quality mattered</small></h1>
                </span>
                <p class="lead indented">
                    Once upon a time&comma; console manufacturers cared about the consumer and made great consoles&period; They vastly outperformed PCs of the era&comma; and had a great selection of games&period;
                </p>
                <p class="indented">
                    This guide isn&rsquo;t trying to hide the truth&period; Back before the 90&rsquo;s&comma; consoles like the SNES&comma; N64&comma; and the rest of the &ldquo;golden age&rdquo; consoles all the way down to the original XBox and PS2&comma; blew PCs out of the water&period; During that time&comma; PCs were barely more than word processors&comma; and cost the average consumer way more than a gaming console&period; Not to mention that back then&comma; consoles were constantly innovating in attempts to outperform each other and provide the best possible system to gamers everywhere&period; Gaming as it is now started with the consoles of the early 80s&period; But&comma; then came 1985&period; The Apple II and Commodore 64 skyrocketed PC popularity&comma; and ever since then&comma; PCs have been gaining in power&comma; game library&comma; and innovation&period; And since the stagnation of the XBox 360 and PS3 era&comma; consoles have been becoming less and less of a good investment&comma; while PCs have just been getting better&period;
                </p>
            </div>
            <div class="container-fluid" id="badconsoles">
                <span class="text-center">
                    <h1>The State of Consoles Today<br>
                    <small>Not so good</small></h1>
                </span>
                <p class="lead indented">
                    With all the benefits of modern PCs&comma; the only things that consoles have going for them are market share&comma; high profitability for the companies building them&comma; expensive games&comma; and exclusives&period; The third being hardly anything to brag about as a console user&period;
                </p>
                <p class="indented">
                    Nowadays&comma; consoles aren&rsquo;t geared towards console users anymore&comma; they&rsquo;re geared towards the companies making them&period; They&rsquo;re made to be as cheap to manufacture and as easy to make a profit as possible&period; And although the XBox 360 and PS3 used new hardware that wasn&rsquo;t used throughout the game industry&comma; which caused their later games to be better due to better optimization&comma; they were really just gimped PCs&period; PCs have always been able to use internet&comma; without paying an extra fee for their device&period; And the services like Netflix and Hulu&comma; that consoles got to use in the last few years before the XBox One and PS4&comma; were on PC first&period; The only thing that consoles have are exclusives like Halo&comma; Gears of War&comma; and Uncharted&period; But those games&comma; and other exclusives&comma; aren&rsquo;t exclusive because that&rsquo;s the only system capable of running it&comma; but rather because Microsoft and Sony use their ill&ndash;earned profits to influence starving developers&period; With Microsoft and Sony dominating the console industry&comma; &lpar;Sorry Nintendo&excl;&rpar; their monopolies just make it worse for everyone involved&period;
                </p>
            </div>
            <div class="container-fluid" id="benefits">
                <section class="pcfact" id="sect1">
                    <span class="text-center">
                        <h1>What PC&rsquo;s have to Offer<br>
                        <small>It&rsquo;s a lot more than you&rsquo;d think</small></h1>
                    </span>
                    <p class="lead indented">
                        Before we continue&comma; <strong>forget everything you thought you knew about PC gaming&period;</strong> The past five years of surging growth&comma; competition&comma; and innovation in the industry have improved the PC in almost every regard&comma; largely due to the mobile revolution and things like <a href="http://store.steampowered.com/">Steam</a>&comma; <a href="https://www.humblebundle.com/">Humble Bundle</a>&comma; and <a href="http://www.gog.com/">Good Old Games</a>&period;
                    </p>
                    <p class="indented">
                        Unlike consoles&comma; PC&rsquo;s can be used for much more than gaming&comma; and are all around awesome pieces of equipment for a ton of reasons&comma; including&comma; but not limited to&colon;
                        <ul>
                            <li>Works with any TV or monitor</li>
                            <li>Works with <a href="http://www.reddit.com/r/pcmasterrace/wiki/controllerguide">any controller</a></li>
                            <li>Better local multiplayer</li>
                            <li>Using a modern GPU yields <a href="http://30vs60.com/">60+ FPS during gameplay</a></li>
                            <li>Usable for work&comma; education&comma; and of course entertainment</li>
                            <li>More powerful than a PS4 or XBox One&comma; even for <a href="http://www.reddit.com/r/pcmasterrace/wiki/builds#wiki_the_next-gen_crusher">&dollar;400</a></li>
                            <li>Cheaper Games &lpar;<a href="http://store.steampowered.com/">Steam</a>&comma; <a href="http://www.gog.com/">GOG</a>&comma; <a href="https://www.humblebundle.com/">Humble Bundle</a>&comma; <a href="http://www.bundlestars.com/">Bundle Stars</a>&rpar;</li>
                            <li>Better average game ratings &lpar;from <a href="http://www.metacritic.com/browse/games/score/metascore/all/pc?sort=desc">Metascore</a>&rpar;</li>
                            <li>More Exclusives</li>
                            <li>Healthy independent developer scene</li>
                            <li>Tons of <a href="http://thepiratebay.se/torrent/9849549/THE_PIRATE_BAY_BUNDLE">free</a> and <a href="http://www.gamesradar.com/best-free-games-play-right-now/">free&ndash;to&ndash;play</a> games</li>
                            <li>Free <a href="http://www.abandonia.com/">abandonware</a> titles</li>
                            <li>10X larger game library</li>
                            <li>Emulate older consoles for free</li>
                            <li>Cheaper hardware</li>
                            <li>Steam Big Picture Mode for couch gaming</li>
                            <li>Easy to repair by yourself without penalty</li>
                            <li>Possible and easy to upgrade</li>
                            <li>Better price:performance ratio than consoles</li>
                            <li>Higher and smoother framerates</li>
                            <li>Higher resolution&comma; from at least native monitor resolution to 4k</li>
                            <li>More grahpical details for immersion</li>
                        </ul>
                    </p>
                    <p class="indented">
                        PCs are always evolving&comma; growing in power&comma; capability&comma; and value every single year&period; No single entity owns the PC&comma; which allows for all these options for PC users to buy&comma; build&comma; and upgrade with&period; And directly because of this freedom from a single corporation&comma; everyone making anything for PC&comma; &lpar;even the people who made this website&rpar;&comma; strive to bring the best product to you, the consumer&period; But&comma; I&rsquo;m guessing that right now you&rsquo;re a staunch console gamer who loves his/her system&period; While that&rsquo;s not necessarily bad&comma; let&rsquo;s get the facts straight on PC gaming&period;
                    </p>
                </section>
                <section class="pcfact" id="sect2">
                    <p class="lead heading">
                        <strong>Isn&rsquo;t PC gaming more expensive than console gaming&quest;</strong>
                    </p>
                    <p class="indented">
                        Not even close&comma; <em>especially</em> if you&rsquo;re trying to target console performance&period; There are <a href="http://www.gamespot.com/articles/149-nvidia-gtx-750-ti-unveiled-plays-titanfall-better-than-xbox-one/1100-6417813/">graphics cards on the market that go for approximately &dollar;150 that surpass PS4 and XBox One performance</a>&comma; and full computers can be built for around &dollar;500&period; Lots of people think that to get a PC that can play games&comma; they need to spend more than &dollar;750&ndash;&dollar;1000&comma; which just <strong>simply isn&rsquo;t true</strong>&period; These inaccuracies are largely due to highly vocal and misinformed masses&comma; and the advertisement of higher end hardware&period; &lpar;Which makes sense when you think about it&comma; companies <em>want</em> to sell their most expensive products&rpar; <a href="https://www.youtube.com/watch?v=RTGMG2qUMjU">We&rsquo;re not living in the 90&rsquo;s anymore</a>&period; In reality&comma; PC hardware is the cheapest it&srquo;s ever been&comma; and anyone&comma; even a complete beginner to PC parts and building&comma; can put together a PC that blows &ldquo;next-gen&rdquo; &lpar;Or is current gen now&quest;&rpar; consoles&comma; which are really just weak PC&rsquo;s with a crippled OS&period; And if all you want to do is match their 720p resolution and 30 FPS&comma; you can do it for the same price as those consoles&comma; but with all the extra things a computer can do&period; In fact&comma; <a href="http://www.gamespot.com/articles/149-nvidia-gtx-750-ti-unveiled-plays-titanfall-better-than-xbox-one/1100-6417813/">it&rsquo;s actually hard to find a minimum&ndash;range graphics card that can even perform as bad as console graphics these days</a>&period; The next time someone reminds you that &ldquo;It&rsquo;s not all about graphics&rdquo;&comma; remind that that they&rsquo;re in luck&comma; because they&rsquo;ll be spending almost nothing on their graphics card to get the performance they deem adequate&period;
                    </p>
                </section>
                <section class="pcfact" id="sect3">
                    <p class="lead heading">
                        <strong>In addition to increased value&comma; PCs also have a higher performance and quality ceiling</strong>
                    </p>
                    <p class="indented">
                        You can build a PC on a budget and beat consoles&comma; performance wise&period; But if you have the money and the desire&comma; you can literally spend thousands&comma; hell millions if you really wanted to&comma; on a computer that would make everyone on Facebook cry in the face of its glory&period; From quad&ndash;graphics cards in SLI/XFire to multi monitor setups&comma; you can build a battlestation worthy of the name&period; And game developers know that there are people out there with those interests&comma; and with them in mind&comma; they make options in games to make E3 demos look like they&rsquo;re from the 90&rsquo;s&period; &lapr;Ok not really&comma; but you get my point&rpar; The misconception here is that some people believe that just because these options exist&comma; they are the only way to play the game&comma; and that they need to buy the latest and greatest &ldquo;RadForce MegaGigaJiggaFlexx 95&ndash;billion Elite Zombie Slayer Edition&rdquo; to play the game on acceptable settings&period; Developers know that the average person is not Bill Gates&comma; and so they make sure that their game is playable on a variety of systems&comma; from the low end laptops to the previously mentioned high end beasts&period; The fact of the matter is&comma; game and software developers know exactly what the average PC specs are&comma; and the things they want most are sales&period; If they made programs so demanding only &dollar;3K&plus; computers could run&comma; they&rsquo;d make a tiny fraction of what they wanted&period; It&rsquo;s almost exclusively the <a href="http://imgur.com/a/KRywv">graphics mods</a> made by people with too much time on their hands &lpar;Which in this case is a great thing&rpar; that push a game&rsquo;s GPU requirement to $dollar;1&comma;000 or more&period;
                    </p>
                </section>
                <section class="pcfact" id="sect4">
                    <p class="lead heading">
                        <strong>You don't have to build a PC to fully experience PC gaming</strong>
                    </p>
                    <p class="indented">
                        Before you run off and buy a computer, know this: <strong>never, ever, ever, for the love of Gaben never buy a "gaming" desktop/laptop</strong>. It's either going to be tremendously overpriced (I'm looking at you, Alienware), or severely underpowered for the price (CyberPowerPC, iBuyPower, and don't even get me started on Apple). Usually, they toss in a good processor such as one from the i7, which is completely overkill for an average gaming build, and toss in a crappy $50 graphics card (the most important part in a gaming computer), causing your games to run like crap. You can put new PC hardware in almost any full-size desktop. What I'm saying is, instead of spending $3200 on that Alienware Aurora (yes they get that high, I almost made the mistake of buying one), you can go buy a $400-$500 desktop from Best Buy and slap in a ~$100 graphis card and play modern games very well. Doing just that is no harder than swapping the hard drive in a console, as was the recent trend, and after a moderate (depending on your internet speed) download and install of the card's drivers, you're good to go.However, building is still the best choice, as it wil last much longer, and offer better customizability and value.
                    </p>
                </section>
                <section class="pcfact" id="sect5">
                    <p class="lead heading">
                        <strong>Building a PC isn't rocket science</strong>
                    </p>
                    <p class="indented">
                        You don't need extensive training, and neither do you need a degree in computerology. All you need to do is watch some video tutorials, or ask someone online, and you'll know all you'll need to build your first PC. Just take care of any static discharge and be ready to delicately handle the parts, and the project can be done in an afternoon.
                    </p>
                </section>
                <section class="pcfact" id="sect6">
                    <p class="lead heading">
                        <strong>Mac OSX and Linux can be used on pretty much any laptop or desktop PC</strong>
                    </p>
                    <p class="indented">
                        A PC made to run OSX is often called a "Hackintosh," and since 2005, people have been making them. Although building does take more effort than clicking the add to cart and buy buttons on Apple's website, and hackintoshes generally take a some work to get running than a standard PC, the money you'd save by building one more than makes up for the time. Check out a great resource for building your own at /r/Hackintosh.
                    </p>
                    <p class="indented">
                        And if Mac isn't your thing, Linux can be used in all its beautiful glory. Among the popular distros are Ubuntu, CrunchBand, Debian, ElementaryOS, and many more.
                    </p>
                </section>
                <section class="pcfact" id="sect7">
                    <p class="lead heading">
                        <strong>PC games are so much cheaper that they alone can make a PC undercut a console within a year</strong>
                    </p>
                    <p class="indented">
                        All year round you can find extreme discounts on all sorts of games. And when I say extreme, I don't mean the 20% Gamestop gives you when you buy 5 other games. I'm talking about 50%, 75%, 90%, even the rare 100% off. With Steam's <em>massive</em> seasonal sales, the great deals at Good Old Games, and the ludiscrously awesome Humble Bundles, you can get libraries of 200+ games in a couple months for less than the price of a console. Even unrealeased games can sometimes get sales, the highest I've seen being 20% off, a year away from release. People save tons of money, some over $3000 over 3-4 years, and even with the crazy low prices, developers still make more money. (Used game sales' profits all go to the seller. None makes its way to the developer, and the practice really hurts the gaming industry. Way more than pirating.)
                    </p>
                </section>
                <section class="pcfact" id="sect8">
                    <p class="lead heading">
                        <strong>The Virtual Reality revolution is being led by the PC</strong>
                    </p>
                    <p class="indented">
                        If you wanted virtual reality gaming (and many other forms of entertainment) in its most immersive form, you'll need a PC. High resolution and framerates are <em>absolutely essential</em> for headsets like the Oculus Rift to properly immerse you. The industry's leading VR company said so themselves: even the next-gen consoles are far too weak to handle they plan on doing. Some people don't want VR, and you may be one them. But simply because you don't want and/or need VR doesn't mean you shouldn't get a PC, it just means that you can save more on your build!
                    </p>
                </section>
                <section class="pcfact" id="sect9">
                    <p class="lead heading">
                        <strong>The ESports revolution is being led by the PC</strong>
                    </p>
                    <p class="indented">
                        This has caused somewhat of an unexpected explosion in PC popularity in recent years, and consoles don't even come close to competing with PCs here, as this video demonstrates.
                    </p>
                </section>
                <section class="pcfact" id="sect10">
                    <p class="lead heading">
                        <strong>I was told that PC has compatibility problems</strong>
                    </p>
                    <p class="indented">
                        Game developers adhere to popular standards when they make their games. Hardware developers adhere to popular standards when they make hardware. Not doing so would gain them a terrible reputation and most likely bankruptcy. Because of this, games on PC don't have the compatibility issues they used to in the DOS days. In the DOS days, there were no standards to adhere to, and there were thousands of different possible hardware configurations. Almost no games worked with every piece of sound, video, and input hardware. The next time you hear someone complain about PC gaming being a pain because they hate 'checking to see if they have enough video RAM' or 'making sure they have a fast enough processor' or 'whether or not their sound drivers are up-to-date', just punch them and link them here. PC game developers make their game compatible with an enormous possible performance spectrum!
                    </p>
                </section>
                <section class="pcfact" id="sect11">
                    <p class="lead heading">
                        <strong>PCs are much better suited to sharing your gaming experience</strong>
                    </p>
                    <p>
                        For starters, you don't need to buy a $200 capture card to record your game, just a $30 program and you can start getting your clips. Not to mention the many other options such as live streaming, video editing (which even for console clips would require a PC to edit them), voice chat programs like Mumble and Teamspeak, video chat like Skype and Google Hangouts, game invites, everything that consoles have to offer, PCs do better. Most clip editors out there for consoles use programs like After Effects and Sony Vegas anyways, so why not cut out the extra hardware and just use a PC, record, and edit all in the same place. Not to mention, most of the demos and trailers that are shown with the "actual gameplay footage" caption are recorded on PCs to make the console version look better than it really is.
                    </p>
                </section>
                <section class="pcfact" id="sect12">
                    <p class="lead heading">
                        <strong>A PC is more family friendly than a console</strong>
                    </p>
                    <p class="indented">
                        Contrary to what commercials might tell you, a PC is much better suited for those wanting a device to entertain all age groups. With options such as multipl displays, terminalized with softXpand, access any streaming service, used for school work, games, from the couch, at a desk, used <em>remotely</em>, for communication, and even emulate console titles using any controller you prefer. You name it, someone out there has made an application to do it. In addition to that, PCs have very in-depth parental/owner controls, that really aren't that hard to use, and are way more secure than the simple solutions that consoles offer.
                    </p>
                </section>
                <section class="pcfact" id="sect13">
                    <p class="lead heading">
                        <strong>A PC is better than a console for media consumption</strong>
                    </p>
                    <p class="indented">
                        The only reason Microsoft and Sony are avertising media consumption (Netflix, Hulu, cable TV, etc) more than games this time is because of how far behind PCs they were. These new consoles make better playback devices than gaming machines. They try to claim that their consoles have access to everything, but really, they don't. <em>Half of all media streaming services on the internet</em> use HTML5 or Flash. Even <em>attempting</em> to access them with the crippled web browsers bundled with consoles requires a subscription first, on top of your internet bill. On PC, there's no extra fees, not to mention the option of using a modern browser. (Sorry Microsoft, but Internet Explorer just doesn't cut it)
                    </p>
                </section>
                <section class="pcfact" id="sect14">
                    <p class="lead heading">
                        <strong>The PC is far more socially capable than a console</strong>
                    </p>
                    <p class="indented">
                        More players per server (look up Planetside 2), more "LAN party-optimized" games like GMod, more game modes, and perfect compatbility between different generations and forms of PC. The pcmasterrace lan guide. 
                    </p>
                </section>
                <section class="pcfact" id="sect15">
                    <p class="lead heading">
                        <strong>PC games support local multiplayer gaming</strong>
                    </p>
                    <p class="indented">
                        Although consoles have traditionally been the platform to offer the best local multiplayer support, that's changing. With the surge in PC power in recent years, there have been games released that fully support both split-screen and "hotseat" multiplayer (nearly all of which also support controller input). Some even support up to 6 simultaneous players. Of course, even games without local multiplayer support can still be played on the same PC... up to 16, in fact. As well as SoftXpand, there's this, which allows screens to be "split" to a second display! Borderlands 2 is one such game that supports local multiplayer... even from the same Steam account.
                    </p>
                </section>
                <section class="pcfact" id="sect16">
                    <p class="lead heading">
                        <strong>You get more online functionality for free than a paid console user</strong>
                    </p>
                    <p class="indented">
                        Steam is a very heavily integrated gaming suite. For example: you can trade coupons, games, items, and cards with another Steam user right from within Steam (no need to be in-game and meet them). You can see what games and servers your friends are currently playing and join the server with the click of a button (and vice versa, you can invite them to yours). You can trade on the Community Market without ever having to hunt someone down that has or wants certain goods. Steam also has an integrated software store, developer store, Workshop (for easier modding), Greenlight, and Big Picture mode for couch gamers. Steam will also automatically sync your saved games and settings to the Steam Cloud. Your in-game Steam panel is pretty awesome, too. It lets you chat, trade, browse the web for walkthroughs and whatnot, track achievements, and much more.
                    </p>
                </section>
                <section class="pcfact" id="sect17">
                    <p class="lead heading">
                        <strong>The PC is better equipped for online play than a console</strong>
                    </p>
                    <p class="indented">
                        Since PCs have more processing power and faster networking speeds (that can be continually upgraded), you have the option of more enemies, more players, more action, quicker and clearer voice communication, lower latency, and much more. Developers and hobbyist server hosts have made great leaps in recent. Thought that 64 player Battelefield was great? Some games have up to <em>10,000</em> players in a single FPS match!
                    </p>
                </section>
                <section class="pcfact" id="sect18">
                    <p class="lead heading">
                        <strong>PC games can be modded, and console games (legally and logically) cannot</strong>
                    </p>
                    <p class="indented">
                        Have you ever beat a game and felt the rush of depression when the last cutscene ends and you're sent back to the main menu? Well, on a console, you'd either be done with the game or just play an almost exact playthrough again. Fortunately for PC gamers, there are a plethora of mods for most of our gaes that extend the replayability for hundreds, even thousands of hours! Mods can add new areas, new gamemodes, new characters, new items, new graphics, and the best part is that they're generally very easy to isntall. This can't be done on a console, without a modded game disc, and then modding your console to accept, which would most likely end with you not being able to play online, and may even get you into some legal trouble. (However, Nintendo allows modding of the Wii, one of the few consoles that are not evil when it comes to modding). And thanks to mods, developers can spend more time on their new games and let the modders keep the game going, and some games even age in reverse, getting better and better looking as the years go on and more mods are made.
                    </p>
                </section>
                <section class="pcfact" id="sect19">
                    <p class="lead heading">
                        <strong>PCs come in many shapes and sizes</strong>
                    </p>
                    <p class="indented">
                        With a console, you're stuck with the design you get, and hope some cool (but overpriced) limited edition console is released or a redesign happens. But with a PC, you can build it small or large, square, round, flat, tall, however you want. <em>You</em> decide what your PC looks like and how best it would fit and look in your home. You can get a PC that's more energy efficient than a console, or one that's quieter (and we're talking dead quiet here). While consoles may be ok in their size and volume, PCs take the cake in every department.
                    </p>
                </section>
                <section class="pcfact" id="sect20">
                    <p class="lead heading">
                        <strong>A <em>Personal Computer</em> can be used as a tax write-off, whereas a <em>gaming</em> console cannot</strong>
                    </p>
                    <p>
                        If you can prove you work on it, you can write it off. Even it has a R9 290x or a Titan Z. As we've seen in recent years, owning a GPU no longer means you're just gaming, what with all the new porudcitivity tools making use of OpenCL and other GPU compute features. If your work software uses it, the IRS has no business saying you can't play on it as well.
                    </p>
                </section>
                <section class="pcfact" id="sect21">
                    <p class="lead heading">
                        <strong>The human eye <em>can</em> see the difference between 30 FPS and 60 FPS</strong>
                    </p>
                    <p class="indented">
                        Contrary to the uneducated hordes you'd find on YouTube comments, you can easily see the difference between 30 and 60 FPS. One major source of this heading (which will soon be fixed) is YouTube itself. People will post "1080p 60 FPS" gameplay, and when people compare that to 30 FPS gameplay on other YouTube videos, they see no difference. This is because YouTube currently caps the framerate at 30, therefore making any videos with a higher framerate a waste of time to compare with. Another source is people's own hardware, though this is mainly when concerning 60+ FPS. Many mainstream monitors are capped between 50 - 60 Hz for their refresh rate, which is how many times the screen can refresh the picture per second. If the refresh rate is lower than your FPS, you'll only see it up to the refresh rate. (And sometimes screen tearing becomes an issue) The last and final source of confusion on this matter is whether or not the human eye can distinguish between the individual frames rather than see them as a slideshow. The answer is no, they cannot... which is the entire point! You can, however, see an improvement in fluidity when the Hz (hertz/refresh rate/FPS) of your system and display are increased. Here's an in-browser 30-vs-60 test if you still don't believe it, and another with actual game footage.
                    </p>
                    <p class="indented">
                        Also, the argument of ~24 FPS providing a cinematic experience is completely false. Back when the film industry started to pick up in the 1930s, any framerate higher would use too much physical film, and any lower made it hard to sync audio. Nowadays, more and more movies are starting to use higher framerates during recording.
                    </p>
                </section>
                <section class="pcfact" id="sect22">
                    <p class="lead heading">
                        <strong>Anything with a framerate below 46 FPS could strain your eyes</strong>
                    </p>
                    <p class="indented">
                        The majority of console titles target 30 (at 1080p) and occasionally a smooth 60 (although at a meager 720p). Due to the severe hardware limitations of the PS4 and XBox One, almost no titles at the time of this writing are capable of both 1080p and 60FPS. Thomas Edison himself concluded that anything below 46FPS will strain the human eye, with strain increasing as the framerate lowers. This was back in the days of film strips, so the bar is probably a bit lower now than it was then, but eye strain still exists and there's a way to avoid it.
                    </p>
                </section>
                <section class="pcfact" id="sect23">
                    <p class="lead heading">
                        <strong>The human eye can see the difference between 60 FOV and 90 FOV</strong>
                    </p>
                    <p class="indented">
                        It's hilarious that some people would try to argue this, yet they sometimes do. The problem with a lot of console titles is that the developers have to narrow the field of view to reduce the stress on their target console's weaker hardware. To give you a glimpse of what you're missing out on, see this image. Although some titles have the same field of view across all platforms, ones that are graphically intensive (like Battlefield 4, Skyrim, COD, etc) will often have a reduced FOV on the console version. The best part of it all, if you really do prefer a lower FOV in a given name or need to lower it for medical reasons (vertigo, etc) the PC version will almost always offer an FOV slider or other way to change the FOV. The PCGamingWiki is and absolutely superb resource for information on how to tweak the FOV (among other things, like resolution and detail).
                    </p>
                </section>
                <section class="pcfact" id="sect24">
                    <p class="lead heading">
                        <strong>Academic studies have proven that higher framerates increase player performance</strong>
                    </p>
                    <p class="indented">
                        Researchers at the University of Massachusetts have determined though observation that not only is the difference noticeable, but player performance increases logarithmically up to 60FPS (logarithmically means the largest increases happen early on and begin to taper off, see rough example here ). Unfortunately, they didn't test with framerates over 60, so we can intelligently theorize (but not scientifically prove) that this also applies to framerates beyond 60. They also concluded (since the curve is logarithmic) that framerates lower than 30 get almost exponentially worse, which is unfortunate because almost every "next-gen" demo so far has shown to dip as low as 10-20FPS while rendering heavier scenes.
                    </p>
                </section>
                <section class="pcfact" id="sect25">
                    <p class="lead heading">
                        <strong>As time goes on, consoles don't get stronger and PCs don't get weaker</strong>
                    </p>
                    <p class="indented">
                        Some people claim that consoles get better performance during their lifecycles due to "optimization" and that PCs somehow get slower and need upgrades to keep up. That's just false. While developers will figure out how to better use the console hardware that they have and may make better looking games after some time, they make the same, if not better, optimizations for PC hardware. And really, the optimization argument only applied to the XBox 360 and PS3, as they used new hardware that the industry was unfamiliar with and needed time to learn. This time around, the XBox One and PS4 are using x86-based processors, an architecture used by PCs for a much longer time.
                    </p>
                </section>
                <section class="pcfact" id="sect26">
                    <p class="lead heading">
                        <strong>After your PC loses its luster after 3-4 years, overclocking can bring it back</strong>
                    </p>
                    <p class="indented">
                        I'm guessing a lot of you console gamers who owned an original PS3 or XBox 360 upgraded to the slim version when they released, for the new performance benefits that they brought. If you had a PC that's starting to get old and can't run games as well as it used to, you can easily overclock, which isn't as hard or as dangerous as it sounds. (Plus, if it's getting old, there's probably no warranty to void anyway) You could always buy new hardware, but with overclocking, you could squeeze out another year or so of use before hitting up Newegg.
                    </p>
                </section>
                <section class="pcfact" id="sect27">
                    <p class="lead heading">
                        <strong>You can share your Steam games with others</strong>
                    </p>
                    <p class="indented">
                        With a console, sharing a game involves giving them a physical copy, which means someone will have to make trip. You also have to trust that they won't damage your disc, or else you no longer will be able to play it without buying a new one. But with Steam Family sharing, you can instantly lend your game to anyone, anywhere in the world. Unlike a physical disc, this way your game can't be broken or stolen. The only downside here is that you need to login with your account on their PC first.
                    </p>
                </section>
                <section class="pcfact" id="sect28">
                    <p class="lead heading">
                        <strong>The games you buy on Steam are yours forever</strong>
                    </p>
                    <p class="indented">
                        Lost your PC? No problem! Just log into whatever client you used and re-download all your games. Another benefit of digital copies.
                    </p>
                </section>
                <section class="pcfact" id="sect29">
                    <p class="lead heading">
                        <strong>PCs are better for couch, TV, and livingroom gaming than consoles</strong>
                    </p>
                    <p class="indented">
                        Just plug your PC into your TV using the HDMI port. Controllers compliment this very well, especially since a PC can use any controller (even a combination of different ones at the same time)! However, mouse and keyboard combinations are just as easy to do if you have a proper piece of furniture to support everything. Here's a video  by BlackBusterCritic explaining how this is possible and fighting the misinformation spreaders that say otherwise. Another big factor is the resolution output of your PC. TVs of today are 1080p and higher; sometimes even 4K (4x as dense as 1080p)! Mainstream consoles can hardly handle 900p, let alone 1080p... Using a PC with these TVs rather than an underpowered console will ensure you hit the level of clarity the TV was designed for, not what the console drags it down to. Any resolution other than a display's native resolution will look blurry in comparison to what it was designed for, and you'll notice this pretty quick when you hook your PC up to your TV.
                    </p>
                </section>
                <section class="pcfact" id="sect30">
                    <p class="lead heading">
                        <strong>You can do a lot more than just gaming on your PC</strong>
                    </p>
                    <p class="indented">
                        I'm going to go ahead and guess a lot of you are reading this from some sort of computer, be it a laptop or desktop. How much did this computer cost? $200? $300? More? All the things you do on this, browsing the web, doing work, can be done on a gaming PC. Instead of spending $400 for a console and another $300 for a computer for work, you can just spend $600 for an awesome computer and have $100 left over for games! (Which during some sales, can get you a lot)
                    </p>
                </section>
                <section class="pcfact" id="sect31">
                    <p class="lead heading">
                        <strong>Both the PS4 and XBox One consoles are still behind mid-range gaming PCs of today</strong>
                    </p>
                    <p class="indented">
                        Modern consoles like the PS4 and XBox One use an enhanced AMD A10, which is a mid-range budget processor. Back when the XBox 360 and PS3 came out, things were different. The XBox 360 and PS3 were both very competitive with equivalent PCs, and they were sold at a tremendous loss, even with their $700 price tags. Consoles now are being sold for $400-$500 and making healthy profits on the hardware alone, (at least after their first single game and round of online access payments, if not immediately) ... and yet they continue to raise the prices for online access/special services and increase their cuts for per-game sales. The last-gen prices aren't even adjusted to inflation, either! It really says something about console innovation when you have to adjust for inflation to compare across generations, doesn't it?
                    </p>
                </section>
                <section class="pcfact" id="sect32">
                    <p class="lead heading">
                        <strong>Console gaming was (at one point) far better than anything PCs had to offer</strong>
                    </p>
                    <p class="indented">
                        We don't try to hide the truth, here. Quite the opposite, actually. Yes, at one point consoles had far better graphical capabilities than the PC. Up until the popularity boom of the Apple II and Commodore 64, PCs were barely more than word processors. But then it became 1985. Consoles have continued to stick around over the years but their time has long passed. The only advantages that consoles have left (since the late 80's) are market share, high profitability for the companies building them, expensive games, and exclusives. The middle being hardly anything to brag about as a console user (hey, look how much I spent on this!). These days, games aren't exclusive simply because that's the only system that can handle it; games are exclusives because Microsoft and Sony use their ill-earned profits to influence starving developers. Especially Microsoft. Look at that, PS4 owner. Microsoft loves you so much that they had to pay the studio money just so they wouldn't release their existing game on your platform. Isn't big business with console monopolies fun?!
                    </p>
                </section>
                <section class="pcfact" id="sect33">
                    <p class="lead heading">
                        <strong>You can open up your PC for cleaning, upgrades and repairs without voiding your warranty</strong>
                    </p>
                    <p class="indented">
                        Let's face it. If your device relies on fan cooling or has vents, it's going to get dusty and eventually very hot. If it contains circuit boards, it's going to be susceptible to power surges and other electrical problems. It doesn't matter who builds the products or where it comes from. No electronic device is invulnerable. Luckily for you, you get to handle things yourself if/when your PC has a problem with an individual part. Consoles aren't so forgiving. If you clean it yourself, the warranty is most likely void. If a part fails, you have to send the entire thing in and wait for it to be sent back. Sometimes you can even continue using the PC while waiting for the individual part to be replaced! Things like RAM cards, hard drives, graphics cards are good examples. Get a PC and you won't have to be afraid of voiding a warranty ever again as long as you're not physically violating the warranty agreement for the part. Repairing is frowned upon with the XBox most of all .
                    </p>
                </section>
                <section class="pcfact" id="sect34">
                    <p class="lead heading">
                        <strong>PC warranties last longer, and are for individual parts, not the whole computer</strong>
                    </p>
                    <p class="indented">
                        Each individual part that you view online has a warranty policy directly from the manufacturer that you can see a summary of. Many parts (like Patriot RAM) even have lifetime warranties. Once you receive the PC components, you'll see that a copy of the warranty (with instructions) is included. If the part goes bad within the warranty period, (1-3 years, sometimes lifetime) the company will mail you back a functional replacement. In comparison, a console requires the entire device to be sent in... sometimes after you've already paid more money for the warranty ! Disassembling it to send them the defective part would not only be difficult because the warranty would instantly be invalidated on the console, but also because the part is useless to you outside of the device (from a warranty standpoint, they won't honor it or even acknowledge the part belongs to their console).
                    </p>
                </section>
                <section class="pcfact" id="sect35">
                    <p class="lead heading">
                        <strong>You can experience beautiful studio-quality voice communication for free using a PC</strong>
                    </p>
                    <p class="indented">
                        With consoles, Steam, and Skype, cell phones, etc, your voice communications are routed through a server and relayed to the recipient. Mumble doesn't do that, it goes straight from point A to point B, which means less latency and less stutter (assuming you're hosting the Murmur server). On a PC, you have the option of hosting a Mumble (or TeamSpeak) server. Mumble is free and open source, and allows you (the server host) to set the bandwidth cap as high as you want. Both the server and client are very lightweight, but if you feel that hosting it yourself won't work you can also rent one. TeamSpeak is also an option, but Mumble is open source and nearly identical. As the host, you can decide how many rooms the server will have, the maximum occupancy, and much more. Just make sure that you port-forward if you host a Mumble server (Murmur) yourself. Oh, you could also join our Mumble server!
                    </p>
                </section>
                <section class="pcfact" id="sect36">
                    <p class="lead heading">
                        <strong>All platforms have their own exclusives but PC has thousands more than any other.</strong>
                    </p>
                    <p class="indented">
                        Due to the healthy, open, and competitive PC market, independent developers tend to gravitate toward the PC. Console companies are generally hostile toward independent developers and not nearly as many can so easily make their game available to console markets. With things like GOG, Steam, torrent sites (No, really! Indie developers and artists have used ThePirateBay for distribution on many occasions), and Desura, developers have millions of PC gamers they can reach without spending a single dime. To play every console exclusive, you would have to buy both consoles anyways (yes, the largest argument is 'console' exclusives not 'PlayStation' or 'XBox', just 'console)... which puts your price point over that of a PC. Not to mention, consoles are what trudged in and created the exclusivity issue in the first place, hoping that you'd reward them for it and buy into their cancerous ecosystem. In a situation where you have a choice of many, pick the best of the group: PC.
                    </p>
                </section>
                <section class="pcfact" id="sect37">
                    <p class="lead heading">
                        <strong>You can use any controller with your PC, not just a single model like with consoles</strong>
                    </p>
                    <p class="indented">
                        First off, mouse and keyboard is the best possible input in terms of accuracy. However, PCs have so many possible input methods, I can't even count them all. You've got Leap Motion, controllers for PS3, Xbox 360, SNES, NES, N64, wheels, joysticks, you name it. Console controllers are a close #2 in gamer preference on a PC. Some games (like super meat boy) can actually be easier with a controller. The point is, on a PC, you have the freedom to use what you want, be it the traditional mouse and keyboard or the latest console controller (which require minimal setup and configuration, plug and play usually works) Isn't freedom of choice a beautiful thing?
                    </p>
                </section>
                <section class="pcfact" id="sect38">
                    <p class="lead heading">
                        <strong>PC is the king of legacy game and software support</strong>
                    </p>
                    <p class="indented">
                        Thanks to emulators and the raw power of modern PCs, you can run any game or application. See our guide to emulation! Your PC can run games from DOS, Mac OS, Commodore, NES, SNES, N64, PS1, PS2, Windows 3.1, Windows 95, 98, 2000, ME, XP, Vista, you name it. Emulators are also known to offer better control configuration (including support for any controller) and graphical options that make it look <em>better</em> than the original. Games from around the 90s will need a virtual machine or DOSBox, and console games would need an emulator. Did I mention that emulators and game ROMs are free? See this video  (6:00 - 7:40) from TekSyndicate for a more in-depth explanation. Here's the latest XBox 360 emulator progress  and here's the latest PS3 emulator progress .
                    </p>
                </section>
                <section class="pcfact" id="sect39">
                    <p class="lead heading">
                        <strong>You can turn one PC into many and multiply its value by "terminalizing" it</strong>
                    </p>
                    <p class="indented">
                        Before you even begin reading, this is a diagram of what it looks like. There are software solutions out there that can essentially add a terminal to your PC and allow a second user to play games and browse in parallel to whatever you're doing without even interrupting each other. All you need is a separate mouse, keyboard, and display. You can turn your single PC into up to 6. SoftXPand is the only software I know that can do this on Windows. For Linux, you have multiseat.
                    </p>
                </section>
                <section class="pcfact" id="sect40">
                    <p class="lead heading">
                        <strong>With a capture card, you can pull video output from another PC or console into a display or window on your main PC</strong>
                    </p>
                    <p class="indented">
                        PCs are capable of a lot, and they can save you a lot of money. One such popular method among PC gamers to access their console without buying a dedicated display is to just send their video into the PC and display it on one of their monitors. It saves power, money, and space.
                    </p>
                </section>
                <section class="pcfact" id="sect41">
                    <p class="lead heading">
                        <strong>A PC can be used to donate spare computational power toward the betterment of mankind...or your wallet</strong>
                    </p>
                    <p class="indented">
                        Seti@Home, Folding@Home and World Community Grid are all a few of the many options you have available as a PC owner. All you have to do is install their clients, hit "Start" and relax. Whenever your PC isn't in use, it uses your otherwise idle CPU to perform small workloads and submit back to their larger projects. You can help cure cancer, find extraterrestrial life, assist in drug research, and many other things. Cheack our our folding team wiki page! This same compute power can be put toward <strong>Bitcoin/Litecoin/Dogecoin mining</strong> as well, earning thousands per year in some cases. 
                    </p>
                </section>
                <section class="pcfact" id="sect42">
                    <p class="lead heading">
                        <strong>PC gaming is strong and growing, and it's not going anywhere for the foreseeable future</strong>
                    </p>
                    <p class="indented">
                        For those of you that worry about jumping onto a dying horse, worry no more. The PC is profitable, stable, and even growing (in some sectors) in market share every year. Not only is it growing worldwide, but the only areas it has left to win a majority gaming marketshare in is Canada, the US, and Mexico. So if you want to go with what's the healthiest for you and the industry...go PC. The misinformation about PC "dying" is typically spewed by angry people that want to recruit others into the ecosystem that they decided was best (probably just because they were already trapped in it). Developers (the people who make the games that drove you to choose a platform in the first place) also prefer PC, because they don't lose half of their profits to manufacturing and exorbitant fees. Lastly and most importantly, gamers are switching back. Ultimately because the PC is cheaper, more open, customizable, easier to fix, and capable of so much more.
                    </p>
                </section>
                <section class="pcfact" id="sect43">
                    <p class="lead heading">
                        <strong>Consoles are no longer the industry innovation leaders</strong>
                    </p>
                    <p class="indented">
                        Back when the SNES/N64/PSX came out, it was a different time. Now, it may seem that consoles are to thank for graphical fidelity making leaps every eight years, but they're not. The reason gaming takes a leap forward every time a new generation of consoles come out is because they're what was holding it back in the first place. Think about it: 70% of the target audience of any given game will have to be playable on continuously aging console hardware. That means that the developers have to make sure that their game will run on said machines, with or without the features they desire. Many of their ideas and features they could have implemented are thrown out the window because the hardware simply can't handle it. As a result, the developers are forced to sell a stripped-down product. Of course, PC users will most often get a better product if the same title lands in their hands, but it's never exactly what the developer originally expected. In some rare cases, the PC port is terrible... but only because the developers are unable to allocate as much time and money to the PC branch of the project. There's a line they have to draw somewhere in between the capabilities of the console and the capabilities of the PC. A fine example of this would be Crysis, a game which launched on consoles four years after it was released on PC . 
                    </p>
                    <p class="indented">
                        There have been occasional instances where the developer will rebuild the game (graphically) from scratch to make sure that the PC version fulfills their vision, but even that is rare. So... the less people that are purchasing consoles, the less of a stranglehold Microsoft and Sony will have on the industry. The more people that are on PC, the more wiggle room the developers have to make their game the best it can be. If you enjoy seeing games get better, go with a PC.
                    </p>
                </section>
                <section class="pcfact" id="sect44">
                    <p class="lead heading">
                        <strong>Hardware companies and game companies make more money from PCs than they do consoles</strong>
                    </p>
                    <p class="indented">
                        Yes, it's true. Even at full price on a console, developers make more money from it being sold on Steam for 75% off [RES ignored duplicate image]. Why? Because developing for PC has no per-sale royalty fees, no development kit fees, no submission fees, and no cost of physically manufacturing/shipping/stocking their finished products. Your money goes straight to the developer's pocket, and the game directly onto your PC. Hardware makers earn much healthier margins on PC as well, since they aren't relying on eventual-profit-by-volume under some tremendously long-term supply contract for Microsoft or Sony. If you care about saving money for yourself [RES ignored duplicate image], go with the PC. If you care about funding developers and keeping your favorite games flowing, go with the PC. If you care about the hardware companies like AMD and nVidia that make modern gaming possible, go with the PC. Once again, consoles exist only to suck money out of developers, hardware companies, and gamers. It's plain as day: Sony and Microsoft don't care about anything but money. If they really cared about improving the state of gaming, why are they sucking every possible entity dry at every possible stage of the process? Literally, for the love of gaming, go with a PC.
                    </p>
                </section>
                <section class="pcfact" id="sect45">
                    <p class="lead heading">
                        <strong>Used game sales are more destructive to the industry than piracy and sales</strong>
                    </p>
                    <p class="indented">
                        According to Lionhead studios, this is true. At least for them, anyways. It makes sense that this would affect all console developers equally, though. This won't be an issue much longer, however, because the console industry is beginning to restrict used game sales to the point where your physical game is more like a receipt than an actual game.
                    </p>
                </section>
                <section class="pcfact" id="sect46">
                    <p class="lead heading">
                        <strong>Piracy is actually more of a problem on consoles than on PC</strong>
                    </p>
                    <p class="indented">
                        There's a lot of undeserved misinformation flying around, usually sounding something like, "PC gamers are a bunch of pirates"/"PC isn't getting this game because they're all pirates"/"Developers did this or that because they're punishing them for being pirates". In reality, as concluded by Intel's research, the majority of piracy you see today takes place on consoles! The massive drop in piracy can likely be credited to easy, cheap, and even free distribution methods and services such as Good Old Games and Steam. Judging by Intel's research, it looks like the only people who really claim piracy is a massive problem on PC are the ones who are known for terrible DRM or hate it purely out of jealousy toward Valve (EA, Ubisoft).
                    </p>
                </section>
                <section class="pcfact" id="sect47">
                    <p class="lead heading">
                        <strong>Most of the screenshots and gameplay you see on "next-gen" advertisements are actually from a PC</strong>
                    </p>
                    <p class="indented">
                        See the image below? That's Microsoft XBox team themselves at a popular game convention using PCs to demonstrate how "good" the XBox One looks! If the XBox team themselves are incapable of convincing you that PC is superior, nothing will. Seeing as all console games are developed on PCs and then skimmed down for consoles, it's only natural that they use footage from the best-looking system to try and sell their product. Sony and Microsoft both know that the truth is far from pretty, so they source their media from a high-end PC rather than a console. They think you'll forget by the time you buy their system anyhow. They don't care about it actually looking good, they just expect you to forget by the time you pay them upwards of $500 for everything. The question is, will you let them?
                    </p>
                </section>
                <section class="pcfact" id="sect48">
                    <p class="lead heading">
                        <strong>Console gamers are not the enemy. Console gaming is</strong>
                    </p>
                    <p class="indented">
                        They give you an inferior system, charge you money for it, and make you hold on to the pieces of junk for 8 years before they give you the option of (measly) upgrades. When you take into account the business strategy of Sony and Microsoft (not so much Nintendo), you'll find that it's all just one big greedy systematic ripoff of uneducated consumers. They're holding your favorite games hostage  on their closed platforms because they know you'll buy into it. If everyone went with a PC and disregarded this disgusting behavior, these developers wouldn't be so easily convinced to support these "next-gen" consoles.. So, do yourself and the industry a favor and go with a PC. It's open, it's cheap, and it's damn powerful. Your best interests are always at hand, no matter how arrogant some of us may seem. For anyone that's offended, I am truly sorry... but if you're going to get offended over this guide then you probably had no intention of giving PC a chance in the first place. Just remember: PC gaming is superior to console gaming, but the gamers themselves are neither superior or inferior to one another: they're just people with varying degrees of understanding.
                    </p>
                </section>
                <section class="pcfact" id="sect49">
                    <p class="lead heading">
                        <strong>Further Reading and other interesting links</strong>
                    </p>
                    <p class="indented">
                        
                    </p>
                </section>
                <section class="pcfact" id="sect50">
                    <p class="lead heading">
                        <strong>Author's note</strong>
                    </p>
                    <p class="indented">
                        Hello, reader! I hope you liked this guide, it's taken me a long time to build. If you liked it, all I ask is that you spread the word. It makes me feel bad knowing that there are people out there with perfectly capable PCs, but they have no idea they can do half of this stuff. It makes me feel even worse knowing that there are people out there that were misled into buying consoles and paying for online access because they had no idea that a measly $100 would have turned their 'office box' into a console crusher. My goal is not to obtain money, karma, or anything else. I just wanted an area to share my knowledge with as many people as possible and I think I've found it here on /r/PCMasterRace. The greatest reward (to me) is knowing that someone was able to make a better purchase because of this guide. Thanks for reading, and if you really liked it or have any questions, please send me a PM and let me know! Contact me to leave feedback.
                    </p>
                </section>
                <section class="pcfact" id="sect51">
                    <p class="lead heading">
                        <strong>Editor's Note</strong>
                    </p>
                    <p class="indented">
                        
                    </p>
                </section>
                <section class="pcfact" id="sect52">
                    <p class="lead heading">
                        <strong>Sharing the guide with others</strong>
                    </p>
                    <p class="indented">
                        Simply print out /r/pcmasterrace/wiki/guide anywhere on Reddit, and the text will automatically link here! Or use some of these flyers!
                    </p>
                </section>
                <section class="pcfact" id="sect53">
                    <p class="lead heading">
                        <strong>Reader Response (For Original Guide)</strong>
                    </p>
                    <p class="indented">
                        
                    </p>
                </section>
                <section class="pcfact" id="sect54">
                    <p class="lead heading">
                        <strong>Translations (Google Powered)</strong>
                    </p>
                    <p class="indented">
                        
                    </p>
                </section>
                <section class="pcfact" id="sect55">
                    <p class="lead heading">
                        <strong>Statistics</strong>
                    </p>
                    <p class="indented">
                        
                    </p>
                </section>
                <section class="pcfact" id="sect56">
                    <p class="lead heading">
                        <strong>Suggestions</strong>
                    </p>
                    <p class="indented">
                        Have a suggestion? <a href="mailto:rudytheninja@gmail.com?subject=Why%20PC?%20Suggestion">Email me</a>!
                    </p>
                </section>
                <section class="pcfact" id="sect57">
                    <p class="lead heading">
                        <strong>Poll</strong>
                    </p>
                    <p class="indented">
                        I'd love it if you answered this.
                    </p>
                </section>
            </div>
        </main>
        <footer>
            Credits<br>
            For menu hamburger Credit to http://codepen.io/icutpeople/pen/Kcxdp<br>
            For icon font: halfings from bootstrap<br>
            For wiki content: get person<br>
            For loading svg : jxnblck loading github<br>
            ROODAY<br>
        </footer>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.0.min.js"><\/script>')</script>
        <script src="js/vendor/bootstrap.min.js"></script>
        <script src="js/plugins.js"></script>
        <script src="videojs/video.js"></script>
        <script>
          videojs.options.flash.swf = "videojs/video-js.swf"
        </script>
        <script>
            (function($){
                $(window).load(function(){
                    $("body, #links").mCustomScrollbar();
                });
            })(jQuery);
        </script>
        <script src="js/easter.js"></script>
        <script src="js/main.js"></script>
    </body>
</html>