<?php

namespace Vulkhan\Toolbox\Filter;

use Vulkhan\Toolbox\Enum\DashboardNoticeType;
use Vulkhan\Toolbox\Wordpress\Filter;

final class DashboardNotice extends Filter
{
    private const DEFAULT_CONFIG = [
        'hook_name' => "admin_notices",
        'message'   => "Message undefined",
        'type'      => DashboardNoticeType::INFO
    ];

	public function __construct( protected array $config, protected int $priority = 10, protected int $acceptedArgs = 2	)
    {
        $this->config['message'] ??= self::DEFAULT_CONFIG['message'];
        $this->config["type"] ??= self::DEFAULT_CONFIG['type'];

        parent::__construct(self::DEFAULT_CONFIG['hook_name'], $priority, $acceptedArgs);
    }

	public function callback(): callable
	{
		return function () : void
		{
			global $pagenow;

			/** only display on dashboard */
			if ( $pagenow !== 'index.php' )
				return;

			/** Only display if use has author role */
			if ( ! \in_array( 'author', \wp_get_current_user()->get_role_caps() ) )
				return;

			?>
			<div class="notice is-dismissible notice-<?php echo $this->config["type"]->value ?>">
				<h2>Toolbox: <?php echo \ucfirst($this->config["type"]->value) ?></h2>

				<div>
					<?php echo $this->config['message'] ?>
				</div>
			</div>
			<?php
		};
	}
}