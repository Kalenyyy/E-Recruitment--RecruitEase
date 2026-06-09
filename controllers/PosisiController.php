<?php

require_once __DIR__ . "/../init.php";

class PosisiController
{
    public static function read()
    {
        global $conn;
        return Posisi::read($conn);
    }

    public static function find($id)
    {
        global $conn;
        return Posisi::find($conn, $id);
    }

    public static function create($nama_posisi, $id_divisi)
    {
        global $conn;
        return Posisi::insert($conn, $nama_posisi, $id_divisi);
    }

    public static function update($id, $nama_posisi, $id_divisi)
    {
        global $conn;
        return Posisi::update($conn, $id, $nama_posisi, $id_divisi);
    }

    public static function delete($id)
    {
        global $conn;
        return Posisi::delete($conn, $id);
    }
}