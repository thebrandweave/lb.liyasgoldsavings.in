<?php

class UserManager {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Authenticate shop user by email or phone
     */
    public function authenticateShopUser($identifier, $password) {
        $stmt = $this->conn->prepare('SELECT CustomerID, CustomerUniqueID, Name, Contact, Email, PasswordHash, Address FROM shop_users WHERE Email = ? OR Contact = ?');
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['PasswordHash'])) {
            return [
                'success' => true,
                'user' => $user
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Invalid email/phone or password'
        ];
    }
    
    /**
     * Check if email exists in shop_db
     */
    public function emailExistsInShopDb($email, $excludeUserId = null) {
        if ($excludeUserId) {
            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM shop_users WHERE Email = ? AND CustomerID != ?');
            $stmt->execute([$email, $excludeUserId]);
        } else {
            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM shop_users WHERE Email = ?');
            $stmt->execute([$email]);
        }
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Check if contact exists in shop_db
     */
    public function contactExistsInShopDb($contact, $excludeUserId = null) {
        if ($excludeUserId) {
            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM shop_users WHERE Contact = ? AND CustomerID != ?');
            $stmt->execute([$contact, $excludeUserId]);
        } else {
            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM shop_users WHERE Contact = ?');
            $stmt->execute([$contact]);
        }
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Create new user in shop_db
     */
    public function createShopUser($name, $email, $contact, $password, $address = '') {
        $customerUniqueID = 'SHOP_' . uniqid() . '_' . time();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare('INSERT INTO shop_users (CustomerUniqueID, Name, Contact, Email, PasswordHash, Address) VALUES (?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$customerUniqueID, $name, $contact, $email, $passwordHash, $address])) {
            return [
                'success' => true,
                'user_id' => $this->conn->lastInsertId(),
                'unique_id' => $customerUniqueID
            ];
        }
        return [
            'success' => false,
            'message' => 'Failed to create user'
        ];
    }
    
    /**
     * Get user by ID from shop_users
     */
    public function getUserById($userId) {
        $stmt = $this->conn->prepare('SELECT CustomerID, CustomerUniqueID, Name, Contact, Email, PasswordHash, Address FROM shop_users WHERE CustomerID = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $user['Source'] = 'shop_db';
        }
        return $user;
    }
    
    /**
     * Update shop user profile
     */
    public function updateShopUser($userId, $name, $email, $contact, $address = '') {
        $stmt = $this->conn->prepare('SELECT CustomerID FROM shop_users WHERE CustomerID = ?');
        $stmt->execute([$userId]);
        if (!$stmt->fetch()) {
            return false;
        }
        $stmt = $this->conn->prepare('SELECT CustomerID FROM shop_users WHERE Email = ? AND CustomerID != ?');
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            return false;
        }
        $stmt = $this->conn->prepare('UPDATE shop_users SET Name = ?, Email = ?, Contact = ?, Address = ? WHERE CustomerID = ?');
        return $stmt->execute([$name, $email, $contact, $address, $userId]);
    }
    
    /**
     * Change password for shop user
     */
    public function changeShopUserPassword($userId, $newPassword) {
        $stmt = $this->conn->prepare('SELECT CustomerID FROM shop_users WHERE CustomerID = ?');
        $stmt->execute([$userId]);
        if (!$stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'User not found in shop database'
            ];
        }
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare('UPDATE shop_users SET PasswordHash = ? WHERE CustomerID = ?');
        if ($stmt->execute([$passwordHash, $userId])) {
            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];
        }
        return [
            'success' => false,
            'message' => 'Failed to change password'
        ];
    }
    
    /**
     * Get all users with pagination (for admin purposes)
     */
    public function getAllUsers($page = 1, $perPage = 10, $search = '') {
        $offset = ($page - 1) * $perPage;
        
        // Build search condition
        $searchCondition = '';
        $params = [];
        if (!empty($search)) {
            $searchCondition = 'WHERE Name LIKE ? OR Email LIKE ? OR Contact LIKE ?';
            $searchParam = '%' . $search . '%';
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        // Get total count
        $countStmt = $this->conn->prepare('SELECT COUNT(*) FROM shop_users ' . $searchCondition);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        
        // Get paginated data - use LIMIT with integer values directly
        $query = 'SELECT CustomerID, CustomerUniqueID, Name, Contact, Email, Address FROM shop_users ' . $searchCondition . ' ORDER BY CustomerID DESC LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset;
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'users' => $users,
            'total' => $totalRecords,
            'pages' => ceil($totalRecords / $perPage),
            'current_page' => $page,
            'per_page' => $perPage
        ];
    }
    
    /**
     * Get shop users with pagination (for admin purposes)
     */
    public function getShopUsers($page = 1, $perPage = 10, $search = '') {
        $offset = ($page - 1) * $perPage;
        
        // Build search condition
        $searchCondition = '';
        $params = [];
        if (!empty($search)) {
            $searchCondition = 'WHERE Name LIKE ? OR Email LIKE ? OR Contact LIKE ?';
            $searchParam = '%' . $search . '%';
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        // Get total count
        $countStmt = $this->conn->prepare('SELECT COUNT(*) FROM shop_users ' . $searchCondition);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        
        // Get paginated data - use LIMIT with integer values directly
        $query = 'SELECT CustomerID, CustomerUniqueID, Name, Contact, Email, Address FROM shop_users ' . $searchCondition . ' ORDER BY CustomerID DESC LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset;
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'users' => $users,
            'total' => $totalRecords,
            'pages' => ceil($totalRecords / $perPage),
            'current_page' => $page,
            'per_page' => $perPage
        ];
    }
    
    /**
     * Delete shop user (admin only)
     */
    public function deleteShopUser($userId) {
        // Verify user exists in shop_db
        $stmt = $this->conn->prepare('SELECT CustomerID FROM shop_users WHERE CustomerID = ?');
        $stmt->execute([$userId]);
        if (!$stmt->fetch()) {
            return false;
        }
        
        // Delete user
        $stmt = $this->conn->prepare('DELETE FROM shop_users WHERE CustomerID = ?');
        return $stmt->execute([$userId]);
    }
} 