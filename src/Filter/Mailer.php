<?php

namespace Vulkhan\Toolbox\Filter;

use Symfony\Component\Mime\Exception\RfcComplianceException;
use Symfony\Component\Mailer\Mailer as SMailer;
use Symfony\Component\Mailer\Transport;
use Vulkhan\Toolbox\Trait\LoggerTrait;
use Vulkhan\Toolbox\Wordpress\Filter;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

// @TODO: Add support for multipart ?
final class Mailer extends Filter
{
    use LoggerTrait;

    private const DEFAULT_CONFIG = [
        'hook_name' => "pre_wp_mail",

        'from'      => [
            'name' => "WordPress",
            'address' => "admin@wordpress.com",
        ],

        'subject'   => "Hey ;)",

        'dsn'       => [
            "smtp://user:pass@smtp.example.com:25"
        ]
    ];

    /** @var Email */
    private Email $email;

    /** @var string */
    private string $contentType = "";

    /**
     * Find the domain name from the URL and get rid of www subdomain if found.
     * @param string $user_at
     * @return string|null
     */
    private function findDomain(string $user_at = "wordpress@") : string | null
    {
        $domain_name = \wp_parse_url( \network_home_url(), PHP_URL_HOST );

        if ( empty( $domain_name ) ) {
            return null;
        }

        return \str_starts_with( $domain_name, "www." ) ? $user_at . \substr( $domain_name, 4 ) : $user_at . $domain_name;
    }

    /**
     * If we don't have an email from the input headers, default to wordpress@$sitename
     * Some hosts will block outgoing mail from this address if it doesn't exist,
     * but there's no easy alternative. Defaulting to admin_email might appear to be
     * another option, but some hosts may refuse to relay mail from an unknown domain.
     * See https://core.trac.wordpress.org/ticket/5007.
     *
     * @return void
     */
    private function defineFrom() : void
    {
        if ( ! empty( $from_list = $this->email->getFrom() ) )
        {
            $headers_from_email = $from_list[0]->getAddress();
            $headers_from_name  = $from_list[0]->getName();

            if ( \count( $from_list ) > 1) {
                $this->logger->notice("More than a single 'From:' was defined in the headers, only the first one will be used." );
            }
        }

        /**
         * Filters the email address to send from.
         *
         * @since 2.2.0
         *
         * @param string $address Email address to send from.
         */
        $address = \apply_filters( 'wp_mail_from', $headers_from_email ?? $this->config['from']['address'] ?? $this->findDomain() ?? self::DEFAULT_CONFIG['from']['address'] );

        /**
         * Filters the name to associate with the "from" email address.
         *
         * @since 2.3.0
         *
         * @param string $name Name associated with the "from" email address.
         */
        $name = \apply_filters( 'wp_mail_from_name', $headers_from_name ?? $this->config['from']['name'] ?? self::DEFAULT_CONFIG['from']['name'] );

        try {
            $this->email->from( new Address( $address, $name ) );
        }

        catch (RfcComplianceException $e)
        {
            $this->logger->warning("Unable to add address From (sender) list, wrong email format.", [
                "from_address"  => $address,
                "from_name"     => $name,
                "message"       => $e->getMessage(),
                "code"          => $e->getCode()
            ]);
        }
    }

    /**
     * Supports all the valid address formats as described in the following page :
     *
     * user@example.com
     * user@example.com, anotheruser@example.com
     * User <user@example.com>
     * User <user@example.com>, Another User <anotheruser@example.com>
     *
     * https://developer.wordpress.org/reference/functions/wp_mail/#valid-address-formats
     *
     * @param string|array $to
     * @return void
     */
    private function parseTo( string|array &$to ) : void
    {
        if ( ! \is_array( $to ) ) {
            $to = \explode(",", $to);
        }

        foreach ( $to as $recipient)
        {
            try
            {
                $this->email->addTo( Address::create( $recipient ) );
            }

            catch (RfcComplianceException $e)
            {
                $this->logger->warning("Unable to Add '$recipient' to the To (recipient) list, wrong email format.", [
                    "recipient" => $recipient,
                    "message"   => $e->getMessage(),
                    "code"      => $e->getCode()
                ]);
            }
        }
    }

    /**
     * Supports all the valid address formats as described in the following page :
     *
     * user@example.com
     * user@example.com, anotheruser@example.com
     * User <user@example.com>
     * User <user@example.com>, Another User <anotheruser@example.com>
     *
     * https://developer.wordpress.org/reference/functions/wp_mail/#valid-address-formats
     *
     * @param string|array $from
     * @return void
     */
    private function parseFrom( string|array &$from ) : void
    {
        if ( ! \is_array( $from ) ) {
            $from = \explode(",", $from);
        }

        foreach ( $from as $sender)
        {
            try
            {
                $this->email->addFrom( Address::create( $sender ) );
            }

            catch (RfcComplianceException $e)
            {
                $this->logger->warning("Unable to Add '$sender' to the From (sender) list, wrong email format.", [
                    "sender"    => $sender,
                    "message"   => $e->getMessage(),
                    "code"      => $e->getCode()
                ]);
            }
        }
    }

    /**
     * @param string $contentType
     * @return void
     */
    private function parseContentType( string &$contentType ) : void
    {
        if ( ! str_contains( $contentType, ';' ) ) {
            return;
        }

        list( $type, $charset ) = explode( ';', $contentType );
        $this->contentType      = trim( $type );

        if ( false !== \apply_filters( 'wp_mail_charset', false ) || str_contains( $charset, 'charset=' ) ) {
            $this->logger->info("Charset cannot be defined manually.");
        }
    }

    /**
     * Supports all the valid address formats as described in the following page :
     *
     * user@example.com
     * user@example.com, anotheruser@example.com
     * User <user@example.com>
     * User <user@example.com>, Another User <anotheruser@example.com>
     *
     * https://developer.wordpress.org/reference/functions/wp_mail/#valid-address-formats
     *
     * @param string|array $cc
     * @return void
     */
    private function parseCC( string|array &$cc ) : void
    {
        if ( ! \is_array( $cc ) ) {
            $cc = \explode(",", $cc);
        }

        foreach ( $cc as $carbon_copy)
        {
            try
            {
                $this->email->addCc( Address::create( $carbon_copy ) );
            }

            catch (RfcComplianceException $e)
            {
                $this->logger->warning("Unable to Add '$carbon_copy' to the Carbon Copy list, wrong email format.", [
                    "carbon_copy"   => $carbon_copy,
                    "message"       => $e->getMessage(),
                    "code"          => $e->getCode()
                ]);
            }
        }
    }

    /**
     * Supports all the valid address formats as described in the following page :
     *
     * user@example.com
     * user@example.com, anotheruser@example.com
     * User <user@example.com>
     * User <user@example.com>, Another User <anotheruser@example.com>
     *
     * https://developer.wordpress.org/reference/functions/wp_mail/#valid-address-formats
     *
     * @param string|array $bcc
     * @return void
     */
    private function parseBCC( string|array &$bcc ) : void
    {
        if ( ! \is_array( $bcc ) ) {
            $bcc = \explode(",", $bcc);
        }

        foreach ( $bcc as $blind_carbon_copy)
        {
            try
            {
                $this->email->addCc( Address::create( $blind_carbon_copy ) );
            }

            catch (RfcComplianceException $e)
            {
                $this->logger->warning("Unable to Add '$blind_carbon_copy' to the Blind Carbon Copy list, wrong email format.", [
                    "blind_carbon_copy" => $blind_carbon_copy,
                    "message"           => $e->getMessage(),
                    "code"              => $e->getCode()
                ]);
            }
        }
    }

    /**
     * Supports all the valid address formats as described in the following page :
     *
     * user@example.com
     * user@example.com, anotheruser@example.com
     * User <user@example.com>
     * User <user@example.com>, Another User <anotheruser@example.com>
     *
     * https://developer.wordpress.org/reference/functions/wp_mail/#valid-address-formats
     *
     * @param string|array $reply_to
     * @return void
     */
    private function parseReplyTo( string|array &$reply_to ) : void
    {
        if ( ! \is_array( $reply_to ) ) {
            $reply_to = \explode(",", $reply_to);
        }

        foreach ( $reply_to as $value)
        {
            try
            {
                $this->email->addReplyTo( Address::create( $value ) );
            }

            catch (RfcComplianceException $e)
            {
                $this->logger->warning("Unable to Add '$value' to the Reply To list, wrong email format.", [
                    "reply_to"  => $value,
                    "message"   => $e->getMessage(),
                    "code"      => $e->getCode()
                ]);
            }
        }
    }

    /**
     * @param string|array $headers
     * @return void
     */
    private function parseHeaders( string|array &$headers ) : void
    {
        if ( ! \is_array( $headers ) ) {
            $headers = \explode( "\n", \str_replace( "\r\n", "\n", $headers ) );
        }

        foreach ( $headers as $header )
        {
            if ( ! str_contains( $header, ':' ) )
            {
                $this->logger->info("Header dismissed", [ 'header' => $header ]);
                continue;
            }

            list( $key, $value ) = explode( ':', trim( $header ), 2 );

            $key    = trim( $key );
            $value  = trim( $value );

            switch ( strtolower( $key ) )
            {
                case 'from':
                    $this->parseFrom($value);
                    break;

                case 'cc':
                    $this->parseCC($value);
                    break;

                case 'bcc':
                    $this->parseBCC($value);
                    break;

                case 'reply-to':
                    $this->parseReplyTo($value);
                    break;

                case 'content-type':
                    $this->parseContentType($value);
                    break;

                default:
                    $this->email->getHeaders()->addTextHeader( $key , $value );
                    $this->logger->info("Text header '$key' added with value '$value'");
                    break;
            }
        }
    }

    /**
     * @param string|array $attachments
     * @return void
     */
    private function parseAttachments( string|array &$attachments ) : void
    {
        if ( ! \is_array( $attachments ) ) {
            $attachments = \explode( "\n", \str_replace( "\r\n", "\n", $attachments ) );
        }

        foreach ( $attachments as $attachment )
        {
            try
            {
                $this->email->attachFromPath($attachment);
            }

            catch (\Exception $e)
            {
                $this->logger->warning("Unable to attach '$attachment' to mail.", [
                    "attachment"    => $attachment,
                    "message"       => $e->getMessage(),
                    "code"          => $e->getCode()
                ]);
            }
        }
    }

	public function __construct( private array $config, protected int $priority = 10, protected int $acceptedArgs = 2 )
    {
        $this->email = new Email();

        $this->initLogger( "Mailer" );

        parent::__construct(self::DEFAULT_CONFIG['hook_name'], $priority, $acceptedArgs);
    }

	public function callback(): callable
	{
        /** if null is returned, wp_mail() will still be executed. */
		return function ( null | bool $return, array $attributes ) : null | bool
		{
            if ( empty( $attributes['to'] ) )
            {
                $this->logger->error("Email cannot be sent without a recipient.", [ 'attributes' => $attributes ]);
                return false;
            }

            $this->parseTo( $attributes['to'] );

            if ( ! empty( $attributes['headers'] ) )
                $this->parseHeaders( $attributes['headers'] );

            if ( ! empty( $attributes['attachments'] ) )
                $this->parseAttachments( $attributes['attachments'] );

            /**
             * From priority :
             * 1. Filters wp_mail_from & wp_mail_from_name
             * 2. Header definition
             * 3. Config variables
             * 4. Domain name extraction
             * 5. Default config variables
             */
            $this->defineFrom();

            $this->email->subject($attributes['subject'] ?? self::DEFAULT_CONFIG['subject']);
            $this->email->text($attributes['message'] ?? "Hello there.");

            if ( \apply_filters( 'wp_mail_content_type', $this->contentType ) === "text/html") {
                $this->email->html($attributes['message'] ?? "<h1>Hello there.</h1>");
            }

            try {
                $mailer = new SMailer( Transport::fromDsn( $this->config['dsn'] ?? self::DEFAULT_CONFIG['dsn'] ) );
                $mailer->send( $this->email );

                /**
                 * Fires after PHPMailer has successfully sent an email.
                 *
                 * The firing of this action does not necessarily mean that the recipient(s) received the
                 * email successfully. It only means that the `send` method above was able to
                 * process the request without any errors.
                 *
                 * @param array $attributes {
                 *     An array containing the email recipient(s), subject, message, headers, and attachments.
                 *
                 * @type string[] $to Email addresses to send message.
                 * @type string $subject Email subject.
                 * @type string $message Message contents.
                 * @type string[] $headers Additional headers.
                 * @type string[] $attachments Paths to files to attach.
                 * }
                 * @since 5.9.0
                 *
                 */
                \do_action('wp_mail_succeeded', $attributes);

                return true;
            }

            catch (\Exception $e) {
                /**
                 * Fires after a PHPMailer\PHPMailer\Exception is caught.
                 *
                 * @param WP_Error $error A WP_Error object with the PHPMailer\PHPMailer\Exception message, and an array
                 *                        containing the mail recipient, subject, message, headers, and attachments.
                 * @since 4.4.0
                 *
                 */
                \do_action('wp_mail_failed', new \WP_Error('wp_mail_failed', $e->getMessage(), $attributes));

                $this->logger->error( $e->getMessage(), [ 'attributes' => $attributes, 'class' => \get_class($e) ] );

                return false;
            }
		};
	}
}