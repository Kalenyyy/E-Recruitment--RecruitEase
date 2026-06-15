<?php

require_once __DIR__ . "/../init.php";

class PosisiController
{
    public static function getTotalCount($conn, $search = '')
    {
        return Posisi::count($conn, $search);
    }

    public static function getPaginated($conn, $page, $perPage, $search = '')
    {
        $offset = ($page - 1) * $perPage;
        return Posisi::readPaginated($conn, $offset, $perPage, $search);
    }

    // Fungsi CRUD standar lainnya tetap sama...
    public static function read()
    {
        global $conn;
        return Posisi::read($conn);
    }
    public static function create($nama, $divisi)
    {
        global $conn;
        return Posisi::insert($conn, $nama, $divisi);
    }
    public static function update($id, $nama, $divisi)
    {
        global $conn;
        return Posisi::update($conn, $id, $nama, $divisi);
    }
    public static function delete($id)
    {
        global $conn;
        return Posisi::delete($conn, $id);
    }
}
