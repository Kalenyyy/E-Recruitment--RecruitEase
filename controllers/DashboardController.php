<?php

class DashboardController
{
    public static function getHRDashboardData($conn)
    {
        return [
            'stats'         => Dashboard::getHRStats($conn),
            'chart_weekly'  => Dashboard::getWeeklyTrend($conn),
            'chart_status'  => Dashboard::getStatusDistribution($conn)
        ];
    }
}
