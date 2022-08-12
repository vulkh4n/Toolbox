<?php

namespace Vulkhan\Toolbox\Enum;

enum DashboardNoticeType: string
{
	case INFO = "info";
	case SUCCESS = "success";
	case WARNING = "warning";
	case ERROR = "error";
}