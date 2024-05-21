<?php
    header("Content-Type: application/json");

    $host = 'localhost';
    $db = 'employee_db';
    $user = 'root';
    $pass = ''; // Ensure this is correct
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getUsers') {
        $stmt = $pdo->query("
            SELECT 
                a.userid, a.username, a.pass, a.email, 
                d.dname, s.totalsalary
            FROM 
                accounts a
            JOIN 
                department d ON a.dept_no = d.dnumber
            JOIN 
                deptsal s ON a.sal_no = s.dnumber
        ");
        $users = $stmt->fetchAll();
        echo json_encode($users);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getDepartments') {
        $stmt = $pdo->query("SELECT dnumber, dname FROM department");
        $departments = $stmt->fetchAll();
        echo json_encode($departments);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getTotalSalary' && isset($_GET['dnumber'])) {
        $dnumber = intval($_GET['dnumber']);
        $stmt = $pdo->prepare("SELECT totalsalary FROM deptsal WHERE dnumber = ?");
        $stmt->execute([$dnumber]);
        $totalsalary = $stmt->fetch();
        echo json_encode($totalsalary);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $sql = "INSERT INTO accounts (username, pass, email, dept_no, sal_no) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$input['username'], $input['pass'], $input['email'], $input['dept_no'], $input['sal_no']]);
        echo json_encode(['message' => 'User added successfully']);
    }
?>
