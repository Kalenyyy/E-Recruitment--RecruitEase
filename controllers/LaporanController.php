<?php
class LaporanController
{
    public static function exportKandidat($conn)
    {
        return Laporan::getKandidatPernahApply($conn);
    }

    public static function exportLowongan($conn)
    {
        return Laporan::getRekapLowongan($conn);
    }

    public static function exportPelamarByJob($conn, $job_id)
    {
        return Laporan::getPelamarByJob($conn, $job_id);
    }

    public static function getJobTitle($conn, $job_id)
    {
        return Laporan::getJobTitle($conn, $job_id);
    }

    public static function exportRekapStatusJob($conn)
    {
        return Laporan::getRekapStatusPerJob($conn);
    }
}
