<?php
/**
 * User Model - Handles all user-related database operations
 * Supports students, teachers, and admins
 */

class User {
    private $pdo;
    
    public function __construct(PDO $pdo): void {
        $this->pdo = $pdo;
    }
    
    /**
     * Create a new user (student or teacher)
     * @param array $data User data
     * @param string $role 'student' or 'teacher'
     * @return array Success status and user ID or error
     */
    public function create(array $data, string $role = 'student'): array {
        try {
            $table = $role === 'teacher' ? 'teachers' : 'students';
            $prefix = $role === 'teacher' ? 'teach_' : 'stu_';
            // Use cryptographically secure random ID
            $id = $prefix . bin2hex(random_bytes(8));
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            if ($role === 'student') {
                $stmt = $this->pdo->prepare("
                    INSERT INTO students (
                        id, username, password, fullName, email, 
                        mobile, address, gradeLevel, createdAt, lastLoginAt
                    ) VALUES (
                        :id, :username, :password, :fullName, :email,
                        :mobile, :address, :gradeLevel, NOW(), NOW()
                    )
                ");
                
                $stmt->execute([
                    ':id' => $id,
                    ':username' => $data['username'],
                    ':password' => $hashedPassword,
                    ':fullName' => $data['fullName'] ?? $data['username'],
                    ':email' => $data['email'],
                    ':mobile' => $data['mobile'] ?? '',
                    ':address' => $data['address'] ?? '',
                    ':gradeLevel' => $data['gradeLevel'] ?? 'Unassigned'
                ]);
            } else {
                $stmt = $this->pdo->prepare("
                    INSERT INTO teachers (
                        id, username, password, fullName, email,
                        mobile, address, specialty, nationalId, createdAt, lastLoginAt
                    ) VALUES (
                        :id, :username, :password, :fullName, :email,
                        :mobile, :address, :specialty, :nationalId, NOW(), NOW()
                    )
                ");
                
                $stmt->execute([
                    ':id' => $id,
                    ':username' => $data['username'],
                    ':password' => $hashedPassword,
                    ':fullName' => $data['fullName'] ?? $data['username'],
                    ':email' => $data['email'],
                    ':mobile' => $data['mobile'] ?? '',
                    ':address' => $data['address'] ?? '',
                    ':specialty' => $data['specialty'] ?? 'Unassigned',
                    ':nationalId' => $data['nationalId'] ?? ''
                ]);
            }
            
            return [
                'success' => true,
                'id' => $id,
                'message' => ucfirst($role) . ' created successfully'
            ];
            
        } catch (PDOException $e) {
            // Check for duplicate username or email
            if ($e->getCode() === '23000') {
                return [
                    'success' => false,
                    'error' => 'Username or email already exists'
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get user by ID
     * @param string $id User ID
     * @param string $role 'student', 'teacher', or 'admin'
     * @return array|null User data or null if not found
     */
    public function getById(string $id, ?string $role = null): ?array {
        try {
            // Determine table from ID prefix if role not provided
            if (!$role) {
                if (strpos($id, 'stu_') === 0) {
                    $role = 'student';
                } elseif (strpos($id, 'teach_') === 0) {
                    $role = 'teacher';
                } elseif (strpos($id, 'admin_') === 0) {
                    $role = 'admin';
                } else {
                    return null;
                }
            }
            
            $table = $this->getTableName($role);
            
            $stmt = $this->pdo->prepare("
                SELECT * FROM $table 
                WHERE id = :id AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                unset($user['password']); // Never return password hash
                $user['role'] = $role;
            }
            
            return $user;
            
        } catch (PDOException $e) {
            error_log("Error getting user by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all users of a specific role
     * @param string $role 'student', 'teacher', or 'admin'
     * @param array $filters Optional filters
     * @return array List of users
     */
    public function getAll(string $role = 'student', array $filters = []): array {
        try {
            $table = $this->getTableName($role);
            
            $sql = "SELECT * FROM $table WHERE (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')";
            $params = [];
            
            // Apply filters
            if (isset($filters['gradeLevel']) && $role === 'student') {
                $sql .= " AND gradeLevel = :gradeLevel";
                $params[':gradeLevel'] = $filters['gradeLevel'];
            }
            
            if (isset($filters['specialty']) && $role === 'teacher') {
                $sql .= " AND specialty = :specialty";
                $params[':specialty'] = $filters['specialty'];
            }
            
            if (isset($filters['search']) && !empty($filters['search'])) {
                $sql .= " AND (username LIKE :search OR fullName LIKE :search OR email LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            $sql .= " ORDER BY createdAt DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Remove passwords from all users
            foreach ($users as &$user) {
                unset($user['password']);
                $user['role'] = $role;
            }
            
            return $users;
            
        } catch (PDOException $e) {
            error_log("Error getting all users: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update user data
     * @param string $id User ID
     * @param array $data Data to update
     * @param string $role User role
     * @return array Success status
     */
    public function update($id, $data, $role = null) {
        try {
            // Determine role from ID if not provided
            if (!$role) {
                if (strpos($id, 'stu_') === 0) {
                    $role = 'student';
                } elseif (strpos($id, 'teach_') === 0) {
                    $role = 'teacher';
                } elseif (strpos($id, 'admin_') === 0) {
                    $role = 'admin';
                }
            }
            
            $table = $this->getTableName($role);
            
            // Build dynamic UPDATE query
            $fields = [];
            $params = [':id' => $id];
            
            // Allowed fields per role
            $allowedFields = [
                'student' => ['username', 'fullName', 'email', 'mobile', 'address', 'gradeLevel', 'password'],
                'teacher' => ['username', 'fullName', 'email', 'mobile', 'address', 'specialty', 'nationalId', 'password'],
                'admin' => ['username', 'name', 'password']
            ];
            
            foreach ($data as $key => $value) {
                // Skip if not allowed field
                if (!in_array($key, $allowedFields[$role])) {
                    continue;
                }
                
                // Hash password if updating
                if ($key === 'password') {
                    $value = password_hash($value, PASSWORD_DEFAULT);
                }
                
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            
            if (empty($fields)) {
                return [
                    'success' => false,
                    'error' => 'No valid fields to update'
                ];
            }
            
            $sql = "UPDATE $table SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'message' => 'User updated successfully'
            ];
            
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return [
                    'success' => false,
                    'error' => 'Username or email already exists'
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete user (soft delete by setting deleted_at timestamp)
     * @param string $id User ID
     * @param string $role User role
     * @param bool $hard Hard delete (actually remove from database)
     * @return array Success status
     */
    public function delete($id, $role = null, $hard = false) {
        try {
            // Determine role from ID if not provided
            if (!$role) {
                if (strpos($id, 'stu_') === 0) {
                    $role = 'student';
                } elseif (strpos($id, 'teach_') === 0) {
                    $role = 'teacher';
                } else {
                    return [
                        'success' => false,
                        'error' => 'Invalid user ID'
                    ];
                }
            }
            
            $table = $this->getTableName($role);
            
            if ($hard) {
                // Actually delete from database
                $stmt = $this->pdo->prepare("DELETE FROM $table WHERE id = :id");
            } else {
                // Soft delete - just mark as deleted
                $stmt = $this->pdo->prepare("UPDATE $table SET deleted_at = NOW() WHERE id = :id");
            }
            
            $stmt->execute([':id' => $id]);
            
            return [
                'success' => true,
                'message' => 'User deleted successfully'
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if username exists
     * @param string $username Username to check
     * @param string $role Role to check in
     * @return bool True if exists
     */
    public function usernameExists($username, $role = 'student') {
        try {
            $table = $this->getTableName($role);
            
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count FROM $table 
                WHERE username = :username 
                AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ");
            $stmt->execute([':username' => $username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error checking username: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if email exists
     * @param string $email Email to check
     * @param string $role Role to check in
     * @return bool True if exists
     */
    public function emailExists($email, $role = 'student') {
        try {
            $table = $this->getTableName($role);
            
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count FROM $table 
                WHERE email = :email 
                AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ");
            $stmt->execute([':email' => $email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error checking email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify password for user
     * @param string $id User ID
     * @param string $password Password to verify
     * @param string $role User role
     * @return bool True if password matches
     */
    public function verifyPassword($id, $password, $role = null) {
        try {
            if (!$role) {
                if (strpos($id, 'stu_') === 0) {
                    $role = 'student';
                } elseif (strpos($id, 'teach_') === 0) {
                    $role = 'teacher';
                } elseif (strpos($id, 'admin_') === 0) {
                    $role = 'admin';
                }
            }
            
            $table = $this->getTableName($role);
            
            $stmt = $this->pdo->prepare("SELECT password FROM $table WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                return password_verify($password, $user['password']);
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error verifying password: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get table name for role
     * @param string $role User role
     * @return string Table name
     */
    private function getTableName($role) {
        $tables = [
            'student' => 'students',
            'teacher' => 'teachers',
            'admin' => 'admins'
        ];
        
        return $tables[$role] ?? 'students';
    }
    
    /**
     * Get user count by role
     * @param string $role User role
     * @return int Count of users
     */
    public function getCount($role = 'student') {
        try {
            $table = $this->getTableName($role);
            
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as count FROM $table 
                WHERE (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['count'];
            
        } catch (PDOException $e) {
            error_log("Error getting user count: " . $e->getMessage());
            return 0;
        }
    }
}
?>
