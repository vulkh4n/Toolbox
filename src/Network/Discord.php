<?php

namespace Vulkhan\Toolbox\Network;

use Vulkhan\Toolbox\Wordpress\Request;

class Discord
{
    public function __construct(protected Request $request) { }

    public function sendMessage($username, $message): array | \WP_Error
    {
        return $this->request->postJson([
            'username'	=> $username,
            'embeds'	=> [
                [
                    'description' => $message,
                ],
            ],
        ]);
    }
}