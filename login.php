<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username=?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            // sukses login
            $_SESSION['login'] = true;
            $_SESSION['username'] = $row['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Tambahkan ini -->
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #4b6cb7 0%, #182848 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: rgba(255,255,255,0.10);
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            border-radius: 16px;
            padding: 40px 32px 32px 32px;
            width: 350px;
            max-width: 95vw;
            display: flex;
            flex-direction: column;
            align-items: center;
            backdrop-filter: blur(6px);
        }
        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo img {
            width: 200px;
            height: 200px;
            object-fit: contain;
        }
        h2 {
            color: #fff;
            margin-bottom: 24px;
            letter-spacing: 1px;
            font-size: 1.4rem;
            text-align: center;
        }
        label {
            color: #e0e0e0;
            font-size: 15px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-top: 6px;
            margin-bottom: 18px;
            border: none;
            border-radius: 8px;
            background: #e3eafc;
            font-size: 15px;
            outline: none;
            transition: box-shadow 0.2s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            box-shadow: 0 0 0 2px #1e3c72;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: linear-gradient(90deg, #16335c 0%, #1e3c72 100%);
        }
        .error-message {
            color: #ffb3b3;
            background: #2c5364;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 18px;
            width: 100%;
            text-align: center;
        }
        @media (max-width: 480px) {
            .login-container {
                padding: 24px 8vw 24px 8vw;
                width: 100%;
                min-width: unset;
                box-sizing: border-box;
            }
            .logo {
                width: 80px;
                height: 80px;
                margin-bottom: 12px;
            }
            .logo img {
                width: 200px;
                height: 200px;
            }
            h2 {
                font-size: 1.1rem;
            }
            label, input[type="text"], input[type="password"], button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <!-- Ganti src logo.png dengan logo Anda -->
            <img src="life_media.png" alt="Logo">
        </div>
        <h2>Login Admin Billing</h2>
        <?php if (!empty($error)) echo "<div class='error-message'>$error</div>"; ?>
        <form method="post">
            <label>Username</label><br>
            <input type="text" name="username" required><br>
            <label>Password</label><br>
            <input type="password" name="password" required><br>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>