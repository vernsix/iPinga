<?php
namespace ipinga;

require_once '/usr/share/php/Mail.php';
require_once '/usr/share/php/Mail/mime.php';

/*
Example:

    \ipinga\email::$host = 'smtp.example.com';
    \ipinga\email::$port = 587;
    \ipinga\email::$auth = true;
    \ipinga\email::$username = 'you@example.com';
    \ipinga\email::$password = 'your_password';
    \ipinga\email::$localhost = 'example.com';
    \ipinga\email::$timeout = 15;
    \ipinga\email::$debug = false;

    \ipinga\email::$from = 'Your Name <you@example.com>';
    \ipinga\email::$recipients[] = 'Joe Blow <joe@blow.com>';
    \ipinga\email::$recipients[] = 'Sister Tammy <tammy@somewhere.com>';
    \ipinga\email::$bcc[] = 'you@example.com';
    \ipinga\email::$subject = 'The subject goes here';
    \ipinga\email::$textBody = 'This is the text body';
    \ipinga\email::$htmlBody = 'This is <b>the HTML</b> body';

    \ipinga\email::send();
*/

class email
{
    /*
    host            - The server to connect. Default is localhost.
    port            - The port to connect. Default is 25.
    auth            - Whether or not to use SMTP authentication. Default is FALSE.
    username        - The username to use for SMTP authentication.
    password        - The password to use for SMTP authentication.
    localhost       - The value to give when sending EHLO or HELO. Default is localhost
    timeout         - The SMTP connection timeout. Default is 15 (15 seconds)
    verp            - Whether to use VERP or not. Default is FALSE.
    debug           - Whether to enable SMTP debug mode or not. Default is FALSE. Mail internally uses Net_SMTP::setDebug .
    persist         - Indicates whether or not the SMTP connection should persist over multiple calls to the send() method.
    pipelining      - Indicates whether or not the SMTP commands pipelining should be used.
    */

    /** @var string */
    public static $host = 'localhost';

    /** @var int */
    public static $port = 25;

    /** @var bool */
    public static $auth = false;

    /** @var string */
    public static $username = '';

    /** @var string */
    public static $password ='';

    /** @var string */
    public static $localhost = 'localhost';

    /** @var int */
    public static $timeout = 15;

    /** @var bool */
    public static $verp;

    /** @var bool */
    public static $debug = false;

    /** @var  bool */
    public static $persist;

    /** @var bool */
    public static $pipelining;



    /** @var string */
    public static $now = '';

    /** @var array */
    public static $headers = array();

    /** @var string */
    public static $from = ''; // format: "Vern Six <vern@vernsix.com>"

    /** @var array */
    public static $recipients = array();

    /** @var array */
    public static $bcc = array();

    /** @var string */
    public static $subject = '';

    /** @var string */
    public static $textBody = '';

    /** @var string */
    public static $htmlBody = '';

    /** @var array */
    public static $attachments = array(); // format: [ $filename => $type, etc ]


    /** @var int */
    public static $error = false;

    /** @var string */
    public static $errorMessage = '';

    /** @var string */
    public static $pearMessage = '';


    public static function send()
    {
        self::$now = date('D, d M Y H:i:s O (T)');
        self::$headers['From'] = self::$from;
        self::$headers['Date'] = self::$now;

        self::$headers['To'] = '';
        foreach (self::$recipients as $r) {
            if (self::$headers['To'] <> '') {
                self::$headers['To'] .= ', ';
            }
            self::$headers['To'] .= $r . ' ';
        }

        self::$headers['Subject'] = self::$subject;

        $mime_params = array();
        /*
            eol             - Type of line end. Default is ""\r\n"".
            delay_file_io   - Specifies if attachment files should be read immediately when adding them into message
                              object or when building the message. Useful for big messages handling using saveMessage
                              functions. Default is "false".
            head_encoding   - Type of encoding to use for the headers of the email. Default is "quoted-printable".
            text_encoding   - Type of encoding to use for the plain text part of the email. Default is "quoted-printable".
            html_encoding   - Type of encoding for the HTML part of the email. Default is "quoted-printable".
            head_charset    - The character set to use for the headers. Default is "iso-8859-1".
            text_charset    - The character set to use for the plain text part of the email. Default is "iso-8859-1".
            html_charset    - The character set to use for the HTML part of the email. Default is "iso-8859-1".
        */
        $mime_params['eol'] = "\n";

        $mime = new \Mail_mime($mime_params);

        // never try to call these lines in reverse order!!  Bad things happen!!
        $mime->setTXTBody( self::$textBody ); // must call first
        $mime->setHTMLBody( self::$htmlBody ); // must call second

        // must add attachments AFTER setting the bodies (above)
        foreach (  self::$attachments as $filename => $type) {
            $mime->addAttachment($filename, $type);
        }

        // this could be used to override the params used above when creating $mime
        //$getparams = array();
        //$getparams["text_encoding"] = '8bit';
        //$b = $mime->get($getparams);
        $mime_body = $mime->get(); // Tell mime to build the message and get the results

        $mime_hdr = $mime->headers(self::$headers);

        $smtpServerDetails = array(
            'host' => gethostbyname(self::$host),
            'port' => self::$port,
            'auth' => self::$auth,
            'username' => self::$username,
            'password' => self::$password,
            'localhost' => self::$localhost,
            'timeout' => self::$timeout,
            'debug' => self::$debug,
        );

        // the precense of these three causes problems on some smtp hosts.  Therefore, I don't set them unless they are
        // specifically set
        if (isset(self::$verp)==true) {
            $smtpServerDetails['verp'] = self::$verp;
        }
        if (isset(self::$persist)==true) {
            $smtpServerDetails['persist'] = self::$persist;
        }
        if (isset(self::$pipelining)==true) {
            $smtpServerDetails['pipelining'] = self::$pipelining;
        }




        $smtp = \Mail::factory('smtp', $smtpServerDetails);

        if (count(self::$bcc)>0) {
            $smtp->send(self::$bcc, $mime_hdr, $mime_body);
        }

        $result = false;
        try {
            $result = $smtp->send(self::$recipients, $mime_hdr, $mime_body);
//            error_log(serialize($result));
        } catch (\Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        if ($result === true) {
            self::$error = false;
            self::$errorMessage = '';
            self::$pearMessage = '';
        } else {
            self::$error = true;
            self::$errorMessage = serialize($result);
            if (\PEAR::isError($result)) {
                self::$pearMessage = $result->getMessage();
            } else {
                self::$pearMessage = '';
            }
        }
        return $result;
    }

}

?>