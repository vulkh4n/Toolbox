<?php
/**
 * Plugin Name: Toolbox
 * Plugin URI: https://toolbox.vulkhan.agency
 * Description: Generic tools used by the Vulkhan Agency
 * Version: 0.2
 * Author: Vulkhan
 * Author URI: https://vulkhan.agency
 */

namespace Vulkhan\Toolbox;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Yaml\Exception\ParseException;
use Vulkhan\Toolbox\Enum\DashboardNoticeType;
use Vulkhan\Toolbox\Filter\DashboardNotice;
use Vulkhan\Toolbox\Filter\GoogleAnalytics;
use Vulkhan\Toolbox\Filter\Mailer;
use Symfony\Component\Yaml\Yaml;

final class Toolbox
{
    /** @var string */
	private const CONFIG_FILE_PATH  = "config/config.yaml";

    /** @var array */
	private static array $config    = [];

    /** @var array */
	private static array $filters   = [];

    /**
     * Returns true if the configuration was successfully loaded.
     * Returns false otherwise.
     * @return bool
     */
	private static function loadConfig(): bool
	{
		try
		{
			self::$config = Yaml::parseFile(\plugin_dir_path( __FILE__ ) . self::CONFIG_FILE_PATH);
		}

		catch (ParseException $e)
		{
			self::$filters["missing_config_file_dashboard_notice"] = new DashboardNotice( [
                'message'   => "Toolbox needs to be configured before use. Please create a valid <strong>" . self::CONFIG_FILE_PATH . "</strong> file.",
                'type'      => DashboardNoticeType::ERROR
            ] );

			return false;
		}

		return true;
	}

    /**
     * Registers Google Analytics filter.
     * @return void
     */
	private static function registerGoogleAnalytics(): void
	{
		if ( ! isset( self::$config["filter"]["google_analytics"] ) )
            return;

		self::$filters["google_analytics"] = new GoogleAnalytics( self::$config["filter"]["google_analytics"] );
	}

    /**
     * Registers Mailer filter.
     * @return void
     */
	private static function registerMailer(): void
	{
		if ( ! isset( self::$config["filter"]["mailer"] ) )
            return;

        self::$filters["mailer"] = new Mailer( self::$config["filter"]["mailer"] );
	}

    /**
     * Bootstraping registered filters
     * @return void
     */
	private static function bootstrapFilters(): void
	{
		foreach ( self::$filters as $filter )
		{
			$filter->add();
		}
	}

    /**
     * Plugin Entry Point main function.
     * @return void
     */
	public static function run(): void
	{
        /**
         * Load Yaml config file, if it fails to load properly
         * Subsequent filters won't be registered as they need configuration.
         */
        if (self::loadConfig())
        {
            self::registerMailer();
            self::registerGoogleAnalytics();
        }

        /** Hooking up registered filters */
        self::bootstrapFilters();
	}
}

Toolbox::run();