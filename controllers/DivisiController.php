<?php

require_once __DIR__ . "/../init.php";

class DivisiController
{
    public static function read()
    {
        global $conn;
        return Divisi::read($conn);
    }

     public static function getTotalCount($conn, $search = '')
    {
        return Divisi::count($conn, $search);
    }

    public static function getPaginated($conn, $page, $perPage, $search = '')
    {
        $offset = ($page - 1) * $perPage;
        return Divisi::readPaginated($conn, $offset, $perPage, $search);
    }

    // KLO BUTUH FUNGSI CARI DIVISI BERDASARKAN ID, BISA PAKE FUNGSI INI

    public static function find($id)
    {
        global $conn;
        return Divisi::find($conn, $id);
    }

    public static function create($nama_divisi)
    {
        global $conn;
        return Divisi::insert($conn, $nama_divisi);
    }

    public static function update($id, $nama_divisi)
    {
        global $conn;
        return Divisi::update($conn, $id, $nama_divisi);
    }

    public static function delete($id)
    {
        global $conn;
        return Divisi::delete($conn, $id);
    }
}