<?php
namespace ipinga;

/*
These are the paths if you install pear manually.  I prefer to use composer, but don't have it called for my composer.json
file in case your app doesn't use this class

require_once '/usr/share/php/Mail.php';
require_once '/usr/share/php/Mail/mime.php';
*/
require_once 'Mail.php';
require_once 'Mail/mime.php';


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
    public static $password = '';

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

    public static $mimeParams = array();


    public static function send()
    {
        // setup the headers.  Stays the same for bcc as well as non-bcc recipients
        self::$now = date('D, d M Y H:i:s O (T)');
        self::$headers['From'] = self::$from;
        self::$headers['Date'] = self::$now;
        self::$headers['To'] = '';
        foreach (self::$recipients as $r) {
            if (self::$headers['To'] <> '') {
                self::$headers['To'] .= ' , ';
            }
            self::$headers['To'] .= $r;
        }
        self::$headers['Subject'] = self::$subject;


        self::$mimeParams = array();
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
        self::$mimeParams['eol'] = "\n";

        $mime = new \Mail_mime(self::$mimeParams);

        // never try to call these lines in reverse order!!  Bad things happen!!
        $mime->setTXTBody(self::$textBody); // must call first
        $mime->setHTMLBody(self::$htmlBody); // must call second

        // must add attachments AFTER setting the bodies (above)
        foreach (self::$attachments as $filename => $type) {
            $mime->addAttachment($filename, $type);
        }


        // this could be used to override the params used above when creating $mime
        //$getparams = array();
        //$getparams["text_encoding"] = '8bit';
        //$b = $mime->get($getparams);
        $mimeBody = $mime->get(); // Tell mime to build the message and get the results
        $mimeHdr = $mime->headers(self::$headers);


        $smtp = \Mail::factory('smtp', self::smtpServerDetailsAsArray());


        // send any BCC emails, but doesn't die if failure sending
        $result = true;
        if (count(self::$bcc) > 0) {
            \ipinga\log::debug('(email) about to send BCC email');
            $result = self::trySmtp($smtp, self::$bcc, $mimeHdr, $mimeBody);
            if ($result===true) {
                \ipinga\log::debug('(email) BCC sent successfully');
            } else {
                \ipinga\log::error('(email) BCC failed');
            }
        }

        // now send to everyone else
        if ($result == true) {
            \ipinga\log::debug('(email) about to send email');
            $result = self::trySmtp($smtp, self::$recipients, $mimeHdr, $mimeBody);
            if ($result===true) {
                \ipinga\log::debug('(email) sent successfully');
            } else {
                \ipinga\log::error('(email) failed');
            }
        }

        return $result;

    }


    public static function trySmtp($smtp, $recipients, $mimeHdr, $mimeBody)
    {
        $result = $smtp->send($recipients, $mimeHdr, $mimeBody);

        if (\PEAR::isError($result)) {
            self::$error = true;
            self::$errorMessage = $result->getMessage();

            foreach (self::smtpServerDetailsAsArray() as $k => $v) {
                \ipinga\log::notice( '(email) server.'. $k. ' => '. $v);
            }

            \ipinga\log::notice('(email) ' . sprintf('%s: code="%d"', $result->getType(), $result->getCode()));
            \ipinga\log::notice('(email) ' . sprintf('%s: message="%s"', $result->getType(), $result->getMessage()));
            \ipinga\log::notice('(email) ' . sprintf('%s: info="%s"', $result->getType(), $result->getUserInfo()));

        } else {
            self::$error = false;
            self::$errorMessage = '';
        }

        return $result;

    }


    public static function smtpServerDetailsAsArray()
    {
        $smtpServerDetails = array(
            'host' => gethostbyname(self::$host),
            'port' => self::$port,
            'auth' => self::$auth,
            'username' => self::$username,
            'password' => self::$password,
            'localhost' => self::$localhost,
            'timeout' => self::$timeout,
            'debug' => self::$debug
        );

        // the precense of these three causes problems on some smtp hosts.  Therefore, I don't set them unless they are
        // specifically set
        if (isset(self::$verp) == true) {
            $smtpServerDetails['verp'] = self::$verp;
        }
        if (isset(self::$persist) == true) {
            $smtpServerDetails['persist'] = self::$persist;
        }
        if (isset(self::$pipelining) == true) {
            $smtpServerDetails['pipelining'] = self::$pipelining;
        }

        return $smtpServerDetails;
    }


}

