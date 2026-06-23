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

    public static function getCandidateDashboardData($conn, $candidateId)
    {
        return [
            'stats'         => Dashboard::getCandidateStats($conn, $candidateId),
            'applications'  => Dashboard::getCandidateApplications($conn, $candidateId),
            'profile_pct'   => Dashboard::getProfileCompletion($conn, $candidateId),
            'recommendations' => Dashboard::getRecommendedJobs($conn, $candidateId),
            'interviews'      => Dashboard::getUpcomingInterviews($conn, $candidateId)
        ];
    }
}
