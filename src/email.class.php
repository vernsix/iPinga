<?php
namespace ipinga;

require_once '/usr/share/php/Mail.php';
require_once '/usr/share/php/Mail/mime.php';

/*
Example:

        $m = new v6_email();
        $m->from = "Vern Six <no-reply@vernsix.com>";
        $m->receipients[] = "Vern Six <vern@vernsix.com>";
        $m->receipients[] = "vernsix@gmail.com";
        $m->subject = 'test message';
        $m->html_body = '<b>This is a test</b>';
        $m->text_body = 'this is silly';
        $m->send();
        echo '<pre>';
        echo var_export($m, true) . "\r\n";
        echo '</pre>';

*/

class email
{

    /** @var array */
    public $receipients = array();

    /** @var array */
    public $bcc = array();

    /** @var array */
    public $headers = array();

    /** @var string */
    public $from = ''; // format: "Vern Six <vern@vernsix.com>"

    /** @var string */
    public $subject = '';

    /** @var string */
    public $now = '';

    /** @var array */
    public $attachments = array(); // format: [ $filename => $type, etc ]

    /** @var string */
    public $html_body = '';

    /** @var string */
    public $text_body = '';

    /** @var int */
    public $error = 0;

    /** @var string */
    public $error_msg = '';

    /** @var string */
    public $pear_message = '';

    /** @var bool */
    public $retval = false;

    /*
        host            - The server to connect. Default is localhost.
        port            - The port to connect. Default is 25.
        auth            - Whether or not to use SMTP authentication. Default is FALSE.
        username        - The username to use for SMTP authentication.
        password        - The password to use for SMTP authentication.
        localhost       - The value to give when sending EHLO or HELO. Default is localhost
        timeout         - The SMTP connection timeout. Default is NULL (no timeout).
        verp            - Whether to use VERP or not. Default is FALSE.
        debug           - Whether to enable SMTP debug mode or not. Default is FALSE. Mail internally uses Net_SMTP::setDebug .
        persist         - Indicates whether or not the SMTP connection should persist over multiple calls to the send() method.
        pipelining      - Indicates whether or not the SMTP commands pipelining should be used.
    */
    public $host = 'localhost';
    public $port = 25;
    public $auth = false;
    public $username = '';
    public $password ='';
    public $localhost = 'localhost';
    public $timeout = null;
    public $verp = false;
    public $debug = false;
    public $persist;
    public $pipelining;

    function send()
    {

        $smtp = array(
            'host' => gethostbyname($this->host),
            'port' => $this->port,
            'auth' => $this->auth,
            'username' => $this->username,
            'password' => $this->password,
            'localhost' => $this->localhost,
            'timeout' => $this->timeout,
            'verp' => $this->verp,
            'debug' => $this->debug,
            'persist' => $this->persist,
            'pipelining' => $this->pipelining
        );

        $this->now = date('D, d M Y H:i:s O (T)');
        $this->headers['From'] = $this->from;
        $this->headers['Date'] = $this->now;
        $this->headers['To'] = '';

        foreach ($this->receipients as $r) {
            if ($this->headers['To'] <> '') {
                $this->headers['To'] = $this->headers['To'] . ', ';
            }
            $this->headers['To'] = $this->headers['To'] . $r . ' ';
        }

        $this->headers['Subject'] = $this->subject;

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

        $mime = new Mail_mime($mime_params);

        // never try to call these lines in reverse order!!  Bad things happen!!
        $mime->setTXTBody($this->text_body); // must call first
        $mime->setHTMLBody($this->html_body); // must call second

        // must add attachments AFTER setting the bodies (above)
        foreach ($this->attachments as $filename => $type) {
            $mime->addAttachment($filename, $type);
        }

        // this could be used to override the params used above when creating $mime
        //$getparams = array();
        //$getparams["text_encoding"] = '8bit';
        //$b = $mime->get($getparams);
        $mime_body = $mime->get(); // Tell mime to build the message and get the results
        $mime_hdr = $mime->headers($this->headers);

        $smtp = Mail::factory('smtp', $smtp);

        if (count($this->bcc)>0) {
            $smtp->send($this->bcc, $mime_hdr, $mime_body);
        }

        try {
            $result = $smtp->send($this->receipients, $mime_hdr, $mime_body);
            error_log(serialize($result));
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        if ($result === true) {
            $this->retval = true;
            $this->error = false;
            $this->error_msg = '';

            $this->pear_message = '';
        } else {
            $this->retval = false;
            $this->error = true;
            $this->error_msg = serialize($result);
            if (PEAR::isError($result)) {
                $this->pear_message = $result->getMessage();
            }
        }
        return $this->retval;
    }

}

?>


























