<?php
include 'db.php';
session_start();

$message = "";

if (isset($_GET['registered'])) {
    $message = "<div class='alert alert-success'>Registration successful! Please login.</div>";
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, role_id, full_name, password_hash, is_active FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $role_id, $full_name, $password_hash, $is_active);
        $stmt->fetch();

        if ($is_active == 0) {
            $message = "<div class='alert alert-danger'>Your account is deactivated.</div>";
        } else if (password_verify($password, $password_hash)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role_id'] = $role_id;
            $_SESSION['full_name'] = $full_name;
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Invalid password!</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>No account found with this email.</div>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Zero Hunger - Sign In</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href=\"img/favicon.ico\" rel=\"icon\">
    <link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">
    <link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>
    <link href=\"https://fonts.googleapis.com/css2?family=Heebo:wght=400;500;600;700&display=swap\" rel=\"stylesheet\">
    <link href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css\" rel=\"stylesheet\">
    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css\" rel=\"stylesheet\">
    <link href=\"css/bootstrap.min.css\" rel=\"stylesheet\">
    <link href=\"css/style.css\" rel=\"stylesheet\">
</head>
<body>
    <div class="container-fluid position-relative bg-white d-flex p-0">
        <div class="container-fluid">
            <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
                <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                    <div class="bg-light rounded p-4 p-sm-5 my-4 mx-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3 class="text-primary"><i class="fa fa-utensils me-2"></i>Zero Hunger</h3>
                            <h3>Sign In</h3>
                        </div>
                        <?php echo $message; ?>
                        <form method="POST" action="">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" name="email" id="floatingInput" placeholder="name@example.com" required>
                                <label for="floatingInput">Email address</label>
                            </div>
                            <div class="form-floating mb-4">
                                <input type="password" class="form-control" name="password" id="floatingPassword" placeholder="Password" required>
                                <label for="floatingPassword">Password</label>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary py-3 w-100 mb-4">Sign In</button>
                            <p class="text-center mb-0">Don't have an Account? <a href="signup.php">Sign Up</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>