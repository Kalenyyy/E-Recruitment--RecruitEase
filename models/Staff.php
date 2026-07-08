<?php
class Staff
{
    public static function allStaff($conn)
    {
        $query = "SELECT staff.*, users.email, users.username 
              FROM staff 
              JOIN users ON staff.user_id = users.id 
              ORDER BY staff.id DESC";
        return mysqli_query($conn, $query);
    }

    // --- TAMBAHAN UNTUK PAGINATION ---
    public static function countAll($conn, $search = '')
    {
        $query = "SELECT COUNT(*) as total FROM staff JOIN users ON staff.user_id = users.id";
        if (!empty($search)) {
            $query .= " WHERE staff.nama_staff LIKE ? OR users.email LIKE ?";
        }

        $stmt = $conn->prepare($query);
        if (!empty($search)) {
            $searchTerm = "%$search%";
            $stmt->bind_param("ss", $searchTerm, $searchTerm);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return $data['total'];
    }

    public static function getPaginated($conn, $limit, $offset, $search = '')
    {
        $query = "SELECT staff.*, users.email, users.username 
              FROM staff 
              JOIN users ON staff.user_id = users.id";

        if (!empty($search)) {
            $query .= " WHERE staff.nama_staff LIKE ? OR users.email LIKE ?";
        }

        $query .= " ORDER BY staff.id DESC LIMIT ? OFFSET ?";

        $stmt = $conn->prepare($query);

        if (!empty($search)) {
            $searchTerm = "%$search%";
            $stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
        } else {
            $stmt->bind_param("ii", $limit, $offset);
        }

        $stmt->execute();
        return $stmt->get_result();
    }
    // --------------------------------

    public static function find($conn, $id)
    {
        $query = "SELECT staff.*, users.email, users.username 
              FROM staff 
              JOIN users ON staff.user_id = users.id 
              WHERE staff.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function findByUserId($conn, $user_id)
    {
        $sql = "SELECT * FROM staff WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function insert($conn, $data)
    {
        $sql = "INSERT INTO staff 
        (user_id, nama_staff, email, alamat, no_telp, jenis_kelamin, tanggal_lahir, foto)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isssssss",
            $data['user_id'],
            $data['nama_staff'],
            $data['email'],
            $data['alamat'],
            $data['no_telp'],
            $data['jenis_kelamin'],
            $data['tanggal_lahir'],
            $data['foto']
        );

        return $stmt->execute();
    }

    public static function updateStatus($conn, $id, $status)
    {
        $sql = "UPDATE staff SET status = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public static function delete($conn, $id)
    {
        $staff = self::find($conn, $id);
        if (!$staff)
            return false;

        if (!empty($staff['foto'])) {
            $fotoPath = __DIR__ . "/../public/uploads/staff/" . $staff['foto'];
            if (file_exists($fotoPath)) {
                unlink($fotoPath);
            }
        }

        $sql = "DELETE FROM staff WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public static function updateProfile($conn, $id, $data)
    {
        $staff = self::find($conn, $id);

        if (!$staff) {
            return false;
        }

        if (!empty($data['foto'])) {

            if (!empty($staff['foto'])) {
                $old = __DIR__ . "/../public/uploads/staff/" . $staff['foto'];

                if (file_exists($old)) {
                    unlink($old);
                }
            }

            $sql = "UPDATE staff
                SET nama_staff=?,
                    email=?,
                    alamat=?,
                    no_telp=?,
                    foto=?
                WHERE id=?";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "sssssi",
                $data['nama_staff'],
                $data['email'],
                $data['alamat'],
                $data['no_telp'],
                $data['foto'],
                $id
            );
        } else {

            $sql = "UPDATE staff
                SET nama_staff=?,
                    email=?,
                    alamat=?,
                    no_telp=?
                WHERE id=?";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "ssssi",
                $data['nama_staff'],
                $data['email'],
                $data['alamat'],
                $data['no_telp'],
                $id
            );
        }

        if (!$stmt->execute()) {
            return false;
        }

        $sqlUser = "UPDATE users
                SET username=?,
                    email=?
                WHERE id=?";

        $stmtUser = $conn->prepare($sqlUser);

        $stmtUser->bind_param(
            "ssi",
            $data['username'],
            $data['email'],
            $staff['user_id']
        );

        return $stmtUser->execute();
    }

    public static function isUniqueExceptMe($conn, $field, $value, $excludeUserId)
    {
        $sql = "SELECT id FROM users WHERE $field = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $value, $excludeUserId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows === 0; // Mengembalikan true jika tidak ada yang pakai (unik)
    }
}
