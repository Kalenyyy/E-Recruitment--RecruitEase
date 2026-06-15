<?php
require_once __DIR__ . "/../init.php";

class SkillController
{
    public static function getAllSkill($conn)
    {
        return Skill::getAll($conn);
    }

    public static function getTotalCount($conn, $search = '')
    {
        return Skill::getTotalCount($conn, $search);
    }

    public static function getPaginatedSkill($conn, $page, $perPage, $search = '')
    {
        return Skill::getPaginated($conn, $page, $perPage, $search);
    }

    public static function getSkillById($conn, $id)
    {
        return Skill::getById($conn, $id);
    }

    public static function createSkill($conn, $nama_skill)
    {
        return Skill::create($conn, $nama_skill);
    }

    public static function updateSkill($conn, $id, $nama_skill)
    {
        return Skill::update($conn, $id, $nama_skill);
    }

    public static function deleteSkill($conn, $id)
    {
        return Skill::delete($conn, $id);
    }
}
