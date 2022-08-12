<?php

namespace Vulkhan\Toolbox\Wordpress;

use Vulkhan\Toolbox\Trait\LoggerTrait;

final class Request
{
    use LoggerTrait;

    public function __construct(protected string $url)
    {
        $this->initLogger("Request" );
    }

    public function postJson(array $json): array | \WP_Error
    {
        $wp_remote_response = \wp_remote_post( $this->url, [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8'
            ],
            'body' => \wp_json_encode($json),
            'method' => 'POST',
            'data_format' => 'body'
        ]);

        if ( \is_wp_error( $wp_remote_response ) )
            $this->logger->error( $wp_remote_response->get_error_message(), ['wp_error_code' => $wp_remote_response->get_error_code() ] );

        return $wp_remote_response;
    }
}