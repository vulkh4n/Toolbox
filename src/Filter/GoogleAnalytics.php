<?php

namespace Vulkhan\Toolbox\Filter;

use Vulkhan\Toolbox\Wordpress\Filter;

final class GoogleAnalytics extends Filter
{
    private const DEFAULT_CONFIG = [
        'tag'       => "UNDEFINED",
        'hook_name' => "wp_head",
    ];

    public function __construct( protected array $config, protected int $priority = 10, protected int $acceptedArgs = 1 )
    {
        $this->config['tag'] ??= self::DEFAULT_CONFIG['tag'];
        $this->config["hook_name"] ??= self::DEFAULT_CONFIG['hook_name'];

        parent::__construct($this->config["hook_name"], $priority, $acceptedArgs);
    }

    public function callback(): callable
    {
        return function () {
        ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $this->config['tag']; ?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());
    gtag('config', '<?php echo $this->config['tag']; ?>');
</script>

        <?php
        };
    }
}